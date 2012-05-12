<?php
namespace Ratchet\Tests\Server;
use Ratchet\Server\IoServer;
use React\EventLoop\StreamSelectLoop;
use React\Socket\Server;
use Ratchet\Tests\Mock\Component;

/**
 * @covers Ratchet\Server\IoServer
 */
class IoServerTest extends \PHPUnit_Framework_TestCase {
    protected $server;

    protected $app;

    protected $port;

    protected $reactor;

    public function setUp() {
        $this->app = new Component;

        $loop = new StreamSelectLoop(0);
        $this->reactor = new Server($loop);
        $this->reactor->listen(0);

        $this->port   = $this->reactor->getPort();
        $this->server = new IoServer($this->app, $this->reactor, $loop);
    }

    public function testOnOpen() {
        $client = stream_socket_client("tcp://localhost:{$this->port}");

        $this->server->loop->tick();

        $this->assertInstanceOf('\\Ratchet\\ConnectionInterface', $this->app->last['onOpen'][0]);
        $this->assertTrue(is_string($this->app->last['onOpen'][0]->remoteAddress));
        $this->assertTrue(is_int($this->app->last['onOpen'][0]->resourceId));
    }

    public function testOnData() {
        $msg = 'Hello World!';

        $client = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($client, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_set_option($client, SOL_SOCKET, SO_SNDBUF, 4096);
        socket_set_block($client);
        socket_connect($client, 'localhost', $this->port);

        $this->server->loop->tick();

        socket_write($client, $msg);
        $this->server->loop->tick();

        socket_shutdown($client, 1);
        socket_shutdown($client, 0);
        socket_close($client);

        usleep(5000);

        $this->server->loop->tick();

        $this->assertEquals($msg, $this->app->last['onMessage'][1]);
    }

    public function testOnClose() {
        $client = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($client, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_set_option($client, SOL_SOCKET, SO_SNDBUF, 4096);
        socket_set_block($client);
        socket_connect($client, 'localhost', $this->port);

        $this->server->loop->tick();

        socket_shutdown($client, 1);
        socket_shutdown($client, 0);
        socket_close($client);

        $this->server->loop->tick();

        usleep(5000);

        $this->assertSame($this->app->last['onOpen'][0], $this->app->last['onClose'][0]);
    }

    public function testFactory() {
        $this->assertInstanceOf('\\Ratchet\\Server\\IoServer', IoServer::factory($this->app, 0));
    }
}