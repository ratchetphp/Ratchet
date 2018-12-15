<?php
namespace Ratchet\Server;

use Ratchet\Server\IoServer;
use React\EventLoop\StreamSelectLoop;
use React\Socket\Server;
use PHPUnit\Framework\TestCase;
/**
 * @covers Ratchet\Server\IoServer
 */
class IoServerTest extends TestCase {
    protected $server;

    protected $app;

    protected $port;

    protected $reactor;

    public function setUp() {
        $this->app = $this->getMockBuilder('\\Ratchet\\MessageComponentInterface')->getMock();

        $loop = new StreamSelectLoop;
        $this->reactor = new Server(0, $loop);

        $uri = $this->reactor->getAddress();
        $this->port   = parse_url((strpos($uri, '://') === false ? 'tcp://' : '') . $uri, PHP_URL_PORT);
        $this->server = new IoServer($this->app, $this->reactor, $loop);
    }

    public function testOnOpen() {
        $this->app->expects($this->once())->method('onOpen')->with($this->isInstanceOf('\\Ratchet\\ConnectionInterface'));

        $client = stream_socket_client("tcp://localhost:{$this->port}");

//        $this->server->loop->tick();

        $this->tick();
        //$this->assertTrue(is_string($this->app->last['onOpen'][0]->remoteAddress));
        //$this->assertTrue(is_int($this->app->last['onOpen'][0]->resourceId));
    }

    public function testOnData() {
        $msg = 'Hello World!';

        $this->app->expects($this->once())->method('onMessage')->with(
            $this->isInstanceOf('\\Ratchet\\ConnectionInterface')
          , $msg
        );

        $client = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($client, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_set_option($client, SOL_SOCKET, SO_SNDBUF, 4096);
        socket_set_block($client);
        socket_connect($client, 'localhost', $this->port);

        $this->tick();

        socket_write($client, $msg);
        $this->tick();

        socket_shutdown($client, 1);
        socket_shutdown($client, 0);
        socket_close($client);

        $this->tick();
    }

    public function testOnClose() {
        $this->app
            ->expects($this->once())
            ->method('onClose')
            ->with($this->isInstanceOf('\\Ratchet\\ConnectionInterface'));

        $client = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($client, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_set_option($client, SOL_SOCKET, SO_SNDBUF, 4096);
        socket_set_block($client);
        socket_connect($client, 'localhost', $this->port);

        $this->tick();
        $this->tick();
        $this->tick();

        socket_shutdown($client, 1);
        socket_shutdown($client, 0);
        socket_close($client);

        $this->tick();
        $this->tick();
        $this->tick();
    }

    public function testFactory() {
        $this->assertInstanceOf('\\Ratchet\\Server\\IoServer', IoServer::factory($this->app, 0));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNoLoopProvidedError() {
        $io   = new IoServer($this->app, $this->reactor);
        $io->run();
    }

    public function testOnErrorPassesException() {
        $conn = $this->getMockBuilder('\\React\\Socket\\ConnectionInterface')->getMock();
        $conn->decor = $this->getMockBuilder('\\Ratchet\\ConnectionInterface')->getMock();
        $err  = new \Exception("Nope");

        $this->app->expects($this->once())->method('onError')->with($conn->decor, $err);

        $this->server->handleError($err, $conn);
    }

    public function onErrorCalledWhenExceptionThrown() {
        $this->markTestIncomplete("Need to learn how to throw an exception from a mock");

        $conn = $this->getMock('\\React\\Socket\\ConnectionInterface');
        $this->server->handleConnect($conn);

        $e = new \Exception;
        $this->app->expects($this->once())->method('onMessage')->with($this->isInstanceOf('\\Ratchet\\ConnectionInterface'), 'f')->will($e);
        $this->app->expects($this->once())->method('onError')->with($this->instanceOf('\\Ratchet\\ConnectionInterface', $e));

        $this->server->handleData('f', $conn);
    }

    private function tick() {
        $this->server->loop->addTimer(0, function () {
            $this->server->loop->stop();
        });
        $this->server->loop->run();
    }
}
