<?php

namespace Ratchet\Http;

use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Mock\Connection;
use Ratchet\WebSocket\WsServer;
use Ratchet\WebSocket\WsServerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use UnexpectedValueException;

/**
 * @covers Ratchet\Http\Router
 */
class RouterTest extends TestCase
{
    protected Router $router;

    protected $matcher;

    protected $connection;

    protected $uri;

    protected $request;

    public function setUp(): void
    {
        $this->connection = $this->createMock(ConnectionInterface::class);
        $this->uri = $this->createMock(UriInterface::class);
        $this->request = $this->createMock(RequestInterface::class);
        $this->request
            ->expects($this->any())
            ->method('getUri')
            ->willReturn($this->uri);
        $this->matcher = $this->createMock(UrlMatcherInterface::class);
        $this->matcher
            ->expects($this->any())
            ->method('getContext')
            ->willReturn($this->createMock(RequestContext::class));
        $this->router = new Router($this->matcher);

        $this->uri->expects($this->any())->method('getPath')->willReturn('ws://doesnt.matter/');
        $this->uri->expects($this->any())->method('withQuery')->with($this->callback(function ($val) {
            $this->setResult($val);

            return true;
        }))->willReturnSelf();
        $this->uri->expects($this->any())->method('getQuery')->willReturnCallback([$this, 'getResult']);
        $this->request->expects($this->any())->method('withUri')->willReturnSelf();
    }

    public function testFourOhFour()
    {
        $this->connection->expects($this->once())->method('close');

        $nope = new ResourceNotFoundException;
        $this->matcher->expects($this->any())->method('match')->will($this->throwException($nope));

        $this->router->onOpen($this->connection, $this->request);
    }

    public function testNullRequest(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->router->onOpen($this->connection);
    }

    public function testControllerIsMessageComponentInterface(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->matcher->expects($this->any())->method('match')->willReturn(['_controller' => new \StdClass]);
        $this->router->onOpen($this->connection, $this->request);
    }

    public function testControllerOnOpen(): void
    {
        $controller = $this->getMockBuilder(WsServer::class)->disableOriginalConstructor();
        $this->matcher->expects($this->any())->method('match')->willReturn(['_controller' => $controller]);
        $this->router->onOpen($this->connection, $this->request);

        $expectedConn = new IsInstanceOf(ConnectionInterface::class);
        $controller->expects($this->once())->method('onOpen')->with($expectedConn, $this->request);

        $this->matcher->expects($this->any())->method('match')->willReturn(['_controller' => $controller]);
        $this->router->onOpen($this->connection, $this->request);
    }

    public function testControllerOnMessageBubbles(): void
    {
        $message = "The greatest trick the Devil ever pulled was convincing the world he didn't exist";
        $controller = $this->getMockBuilder(WsServer::class)->disableOriginalConstructor();
        $controller->expects($this->once())->method('onMessage')->with($this->connection, $message);

        $this->connection->controller = $controller;

        $this->router->onMessage($this->connection, $message);
    }

    public function testControllerOnCloseBubbles(): void
    {
        $controller = $this->getMockBuilder(WsServer::class)->disableOriginalConstructor();
        $controller->expects($this->once())->method('onClose')->with($this->connection);

        $this->connection->controller = $controller;

        $this->router->onClose($this->connection);
    }

    public function testControllerOnErrorBubbles(): void
    {
        $exception = new \Exception('One cannot be betrayed if one has no exceptions');
        $controller = $this->getMockBuilder(WsServer::class)->disableOriginalConstructor();
        $controller->expects($this->once())->method('onError')->with($this->connection, $exception);

        $this->connection->controller = $controller;

        $this->router->onError($this->connection, $exception);
    }

    public function testRouterGeneratesRouteParameters(): void
    {
        /** @var WsServerInterface $controller */
        $controller = $this->getMockBuilder(WsServer::class)->disableOriginalConstructor();

        /** @var UrlMatcherInterface $matcher */
        $this->matcher->expects($this->any())->method('match')->willReturn(['_controller' => $controller, 'foo' => 'bar', 'baz' => 'qux']);

        /** @var Connection $connection */
        $connection = $this->createMock(Connection::class);

        $router = new Router($this->matcher);

        $router->onOpen($connection, $this->request);

        $this->assertEquals('foo=bar&baz=qux', $this->request->getUri()->getQuery());
    }

    public function testQueryParams(): void
    {
        $controller = $this->getMockBuilder(WsServer::class)->disableOriginalConstructor();
        $this->matcher->expects($this->any())
            ->method('match')
            ->willReturn(['_controller' => $controller, 'foo' => 'bar', 'baz' => 'qux']);

        $connection = $this->createMock(Connection::class);
        $request = $this->createMock(RequestInterface::class);
        $uri = new \GuzzleHttp\Psr7\Uri('ws://doesnt.matter/endpoint?hello=world&foo=nope');

        $request->expects($this->any())->method('getUri')->willReturnCallback(function () use (&$uri) {
            return $uri;
        });
        $request->expects($this->any())->method('withUri')->with($this->callback(function ($url) use (&$uri) {
            $uri = $url;

            return true;
        }))->willReturnSelf();

        $router = new Router($this->matcher);
        $router->onOpen($connection, $request);

        $this->assertEquals('foo=nope&baz=qux&hello=world', $request->getUri()->getQuery());
        $this->assertEquals('ws', $request->getUri()->getScheme());
        $this->assertEquals('doesnt.matter', $request->getUri()->getHost());
    }

    public function testImpatientClientOverflow(): void
    {
        $this->connection->expects($this->once())->method('close');

        $header = "GET /nope HTTP/1.1
Upgrade: websocket                                   
Connection: upgrade                                  
Host: localhost                                 
Origin: http://localhost                        
Sec-WebSocket-Version: 13\r\n\r\n";

        $app = new HttpServer(new Router(new UrlMatcher(new RouteCollection, new RequestContext)));
        $app->onOpen($this->connection);
        $app->onMessage($this->connection, $header);
        $app->onMessage($this->connection, 'Silly body');
    }
}
