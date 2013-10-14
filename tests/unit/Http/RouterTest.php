<?php
namespace Ratchet\Http;
use Ratchet\Http\Router;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * @covers Ratchet\Http\Router
 */
class RouterTest extends \PHPUnit_Framework_TestCase {
    protected $_router;
    protected $_matcher;
    protected $_conn;
    protected $_req;

    public function setUp() {
        $this->_conn    = $this->getMock('\Ratchet\ConnectionInterface');
        $this->_req     = $this->getMock('\Guzzle\Http\Message\RequestInterface');
        $this->_matcher = $this->getMock('Symfony\Component\Routing\Matcher\UrlMatcherInterface');
        $this->_matcher
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($this->getMock('Symfony\Component\Routing\RequestContext')));
        $this->_router  = new Router($this->_matcher);

        $this->_req->expects($this->any())->method('getPath')->will($this->returnValue('/whatever'));
    }

    public function testFourOhFour() {
        $this->_conn->expects($this->once())->method('close');

        $nope = new ResourceNotFoundException;
        $this->_matcher->expects($this->any())->method('match')->will($this->throwException($nope));

        $this->_router->onOpen($this->_conn, $this->_req);
    }

    public function testNullRequest() {
        $this->setExpectedException('\UnexpectedValueException');
        $this->_router->onOpen($this->_conn);
    }

    public function testControllerIsMessageComponentInterface() {
        $this->setExpectedException('\UnexpectedValueException');
        $this->_matcher->expects($this->any())->method('match')->will($this->returnValue(array('_controller' => new \StdClass)));
        $this->_router->onOpen($this->_conn, $this->_req);
    }

    public function testControllerOnOpen() {
        $controller = $this->getMockBuilder('\Ratchet\WebSocket\WsServer')->disableOriginalConstructor()->getMock();
        $this->_matcher->expects($this->any())->method('match')->will($this->returnValue(array('_controller' => $controller)));
        $this->_router->onOpen($this->_conn, $this->_req);

        $expectedConn = new \PHPUnit_Framework_Constraint_IsInstanceOf('\Ratchet\ConnectionInterface');
        $controller->expects($this->once())->method('onOpen')->with($expectedConn, $this->_req);

        $this->_matcher->expects($this->any())->method('match')->will($this->returnValue(array('_controller' => $controller)));
        $this->_router->onOpen($this->_conn, $this->_req);
    }

    public function testControllerOnMessageBubbles() {
        $message = "The greatest trick the Devil ever pulled was convincing the world he didn't exist";
        $controller = $this->getMockBuilder('\Ratchet\WebSocket\WsServer')->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('onMessage')->with($this->_conn, $message);

        $this->_conn->controller = $controller;

        $this->_router->onMessage($this->_conn, $message);
    }

    public function testControllerOnCloseBubbles() {
        $controller = $this->getMockBuilder('\Ratchet\WebSocket\WsServer')->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('onClose')->with($this->_conn);

        $this->_conn->controller = $controller;

        $this->_router->onClose($this->_conn);
    }

    public function testControllerOnErrorBubbles() {
        $e= new \Exception('One cannot be betrayed if one has no exceptions');
        $controller = $this->getMockBuilder('\Ratchet\WebSocket\WsServer')->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('onError')->with($this->_conn, $e);

        $this->_conn->controller = $controller;

        $this->_router->onError($this->_conn, $e);
    }
}
