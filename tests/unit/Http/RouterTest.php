<?php
namespace Ratchet\Http;
use PHPUnit\Framework\TestCase;
use Ratchet\WebSocket\WsServerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;

/**
 * @covers Ratchet\Http\Router
 */
class RouterTest extends TestCase {
    protected $_router;
    protected $_matcher;
    protected $_conn;
    protected $_uri;
    protected $_req;

    /**
     * @before
     */
    public function setUpConnection() {
        $this->_conn = $this->getMockBuilder('Ratchet\Mock\Connection')->getMock();
        $this->_uri  = $this->getMockBuilder('Psr\Http\Message\UriInterface')->getMock();
        $this->_req  = $this->getMockBuilder('Psr\Http\Message\RequestInterface')->getMock();
        $this->_req
            ->expects($this->any())
            ->method('getUri')
            ->will($this->returnValue($this->_uri));
        $this->_matcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\UrlMatcherInterface')->getMock();
        $this->_matcher
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($this->getMockBuilder('Symfony\Component\Routing\RequestContext')->getMock()));
        $this->_router  = new Router($this->_matcher);

        $this->_uri->expects($this->any())->method('getPath')->will($this->returnValue('ws://doesnt.matter/'));
        $this->_uri->expects($this->any())->method('withQuery')->with($this->callback(function($val) {
            $this->setResult($val);

            return true;
        }))->will($this->returnSelf());
        $this->_uri->expects($this->any())->method('getHost')->willReturn('example.com');
        $this->_req->expects($this->any())->method('withUri')->will($this->returnSelf());
        $this->_req->expects($this->any())->method('getMethod')->willReturn('GET');
    }

    public function testFourOhFour() {
        $this->_conn->expects($this->once())->method('close');

        $nope = new ResourceNotFoundException;
        $this->_matcher->expects($this->any())->method('match')->will($this->throwException($nope));

        $this->_router->onOpen($this->_conn, $this->_req);
    }

    public function testNullRequest() {
        if (method_exists($this, 'expectException')) {
            $this->expectException('UnexpectedValueException');
        } else {
            $this->setExpectedException('UnexpectedValueException');
        }
        $this->_router->onOpen($this->_conn);
    }

    public function testControllerIsMessageComponentInterface() {
        if (method_exists($this, 'expectException')) {
            $this->expectException('UnexpectedValueException');
        } else {
            $this->setExpectedException('UnexpectedValueException');
        }
        $this->_matcher->expects($this->any())->method('match')->will($this->returnValue(array('_controller' => new \StdClass)));
        $this->_router->onOpen($this->_conn, $this->_req);
    }

    public function testControllerOnOpen() {
        $controller = $this->getMockBuilder('Ratchet\WebSocket\WsServer')->disableOriginalConstructor()->getMock();
        $this->_matcher->expects($this->any())->method('match')->will($this->returnValue(array('_controller' => $controller)));
        $this->_router->onOpen($this->_conn, $this->_req);

        $expectedConn = $this->isInstanceOf('Ratchet\Mock\Connection');
        $controller->expects($this->once())->method('onOpen')->with($expectedConn, $this->_req);

        $this->_matcher->expects($this->any())->method('match')->will($this->returnValue(array('_controller' => $controller)));
        $this->_router->onOpen($this->_conn, $this->_req);
    }

    public function testControllerOnMessageBubbles() {
        $message = "The greatest trick the Devil ever pulled was convincing the world he didn't exist";
        $controller = $this->getMockBuilder('Ratchet\WebSocket\WsServer')->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('onMessage')->with($this->_conn, $message);

        $this->_conn->controller = $controller;

        $this->_router->onMessage($this->_conn, $message);
    }

    public function testControllerOnCloseBubbles() {
        $controller = $this->getMockBuilder('Ratchet\WebSocket\WsServer')->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('onClose')->with($this->_conn);

        $this->_conn->controller = $controller;

        $this->_router->onClose($this->_conn);
    }

    public function testControllerOnErrorBubbles() {
        $e= new \Exception('One cannot be betrayed if one has no exceptions');
        $controller = $this->getMockBuilder('Ratchet\WebSocket\WsServer')->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('onError')->with($this->_conn, $e);

        $this->_conn->controller = $controller;

        $this->_router->onError($this->_conn, $e);
    }

    public function testRouterGeneratesRouteParameters() {
        /** @var $controller WsServerInterface */
        $controller = $this->getMockBuilder('Ratchet\WebSocket\WsServer')->disableOriginalConstructor()->getMock();
        /** @var $matcher UrlMatcherInterface */
        $this->_matcher->expects($this->any())->method('match')->will(
            $this->returnValue(['_controller' => $controller, 'foo' => 'bar', 'baz' => 'qux'])
        );
        $conn = $this->getMockBuilder('Ratchet\Mock\Connection')->getMock();

        $this->_uri->expects($this->once())->method('withQuery')->with('foo=bar&baz=qux')->willReturnSelf();
        $this->_req->expects($this->once())->method('withUri')->will($this->returnSelf());

        $router = new Router($this->_matcher);

        $router->onOpen($conn, $this->_req);
    }

    public function testQueryParams() {
        $controller = $this->getMockBuilder('Ratchet\WebSocket\WsServer')->disableOriginalConstructor()->getMock();
        $this->_matcher->expects($this->any())->method('match')->will(
            $this->returnValue(['_controller' => $controller, 'foo' => 'bar', 'baz' => 'qux'])
        );

        $conn    = $this->getMockBuilder('Ratchet\Mock\Connection')->getMock();
        $request = $this->getMockBuilder('Psr\Http\Message\RequestInterface')->getMock();
        $uri = new \GuzzleHttp\Psr7\Uri('ws://doesnt.matter/endpoint?hello=world&foo=nope');

        $request->expects($this->any())->method('getUri')->will($this->returnCallback(function() use (&$uri) {
            return $uri;
        }));
        $request->expects($this->any())->method('withUri')->with($this->callback(function($url) use (&$uri) {
            $uri = $url;

            return true;
        }))->will($this->returnSelf());
        $request->expects($this->once())->method('getMethod')->willReturn('GET');

        $router = new Router($this->_matcher);
        $router->onOpen($conn, $request);

        $this->assertEquals('foo=nope&baz=qux&hello=world', $request->getUri()->getQuery());
        $this->assertEquals('ws', $request->getUri()->getScheme());
        $this->assertEquals('doesnt.matter', $request->getUri()->getHost());
    }

    public function testImpatientClientOverflow() {
        $this->_conn->expects($this->once())->method('close');

        $header = "GET /nope HTTP/1.1
Upgrade: websocket                                   
Connection: upgrade                                  
Host: localhost                                 
Origin: http://localhost                        
Sec-WebSocket-Version: 13\r\n\r\n";

        $app = new HttpServer(new Router(new UrlMatcher(new RouteCollection, new RequestContext)));
        $app->onOpen($this->_conn);
        $app->onMessage($this->_conn, $header);
        $app->onMessage($this->_conn, 'Silly body');
    }
}
