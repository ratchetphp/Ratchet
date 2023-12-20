<?php

namespace Ratchet\Server;

use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;
use React\Socket\ConnectionInterface as SocketConnectionInterface;
use React\Socket\SocketServer;
use RuntimeException;

/**
 * @covers Ratchet\Server\IoServer
 */
class IoServerTest extends TestCase
{
    protected IoServer $server;

    protected MessageComponentInterface $app;

    protected int $port;

    protected SocketServer $reactor;

    protected function tickLoop(LoopInterface $loop)
    {
        $loop->futureTick(function () use ($loop) {
            $loop->stop();
        });

        $loop->run();
    }

    public function setUp(): void
    {
        $this->app = $this->createMock(MessageComponentInterface::class);

        $loop = new StreamSelectLoop;
        $this->reactor = new SocketServer(0, [], $loop);

        $uri = $this->reactor->getAddress();
        $this->port = parse_url((strpos($uri, '://') === false ? 'tcp://' : '').$uri, PHP_URL_PORT);
        $this->server = new IoServer($this->app, $this->reactor, $loop);
    }

    public function testOnOpen(): void
    {
        $this->app->expects($this->once())->method('onOpen')->with($this->isInstanceOf(ConnectionInterface::class));
        $this->tickLoop($this->server->loop);
    }

    public function testOnData(): void
    {
        $message = 'Hello World!';

        $this->app->expects($this->once())->method('onMessage')->with(
            $this->isInstanceOf(ConnectionInterface::class),
            $message
        );

        $client = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($client, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_set_option($client, SOL_SOCKET, SO_SNDBUF, 4096);
        socket_set_block($client);
        socket_connect($client, 'localhost', $this->port);

        $this->tickLoop($this->server->loop);

        socket_write($client, $message);
        $this->tickLoop($this->server->loop);

        socket_shutdown($client, 1);
        socket_shutdown($client, 0);
        socket_close($client);

        $this->tickLoop($this->server->loop);
    }

    public function testOnClose(): void
    {
        $this->app->expects($this->once())->method('onClose')->with($this->isInstanceOf(ConnectionInterface::class));

        $client = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($client, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_set_option($client, SOL_SOCKET, SO_SNDBUF, 4096);
        socket_set_block($client);
        socket_connect($client, 'localhost', $this->port);

        $this->tickLoop($this->server->loop);

        socket_shutdown($client, 1);
        socket_shutdown($client, 0);
        socket_close($client);

        $this->tickLoop($this->server->loop);
    }

    public function testFactory(): void
    {
        $this->assertInstanceOf(IoServer::class, IoServer::factory($this->app, 0));
    }

    public function testNoLoopProvidedError(): void
    {
        $this->expectException(RuntimeException::class);

        $io = new IoServer($this->app, $this->reactor);
        $io->run();
    }

    public function testOnErrorPassesException()
    {
        $connection = $this->createMock(SocketConnectionInterface::class);
        $connection->decor = $this->createMock(ConnectionInterface::class);
        $err = new \Exception('Nope');

        $this->app->expects($this->once())->method('onError')->with($connection->decor, $err);

        $this->server->handleError($err, $connection);
    }

    public function onErrorCalledWhenExceptionThrown()
    {
        $this->markTestIncomplete('Need to learn how to throw an exception from a mock');

        $connection = $this->createMock(SocketConnectionInterface::class);
        $this->server->handleConnect($connection);

        $exception = new \Exception;
        $this->app->expects($this->once())->method('onMessage')->with($this->isInstanceOf(ConnectionInterface::class), 'f')->will($exception);
        $this->app->expects($this->once())->method('onError')->with($this->isInstanceOf(ConnectionInterface::class, $exception));

        $this->server->handleData('f', $connection);
    }
}
