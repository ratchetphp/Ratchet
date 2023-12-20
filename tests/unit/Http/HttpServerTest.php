<?php

namespace Ratchet\Http;

use Ratchet\AbstractMessageComponentTestCase;
use Ratchet\ConnectionInterface;

/**
 * @covers Ratchet\Http\HttpServer
 */
class HttpServerTest extends AbstractMessageComponentTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->connection->httpHeadersReceived = true;
    }

    public function getConnectionClassString(): string
    {
        return ConnectionInterface::class;
    }

    public function getDecoratorClassString(): string
    {
        return HttpServer::class;
    }

    public function getComponentClassString(): string
    {
        return HttpServerInterface::class;
    }

    public function testOpen(): void
    {
        $headers = "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n";

        $this->connection->httpHeadersReceived = false;
        $this->app->expects($this->once())->method('onOpen')->with($this->isExpectedConnection());
        $this->server->onMessage($this->connection, $headers);
    }

    public function testOnMessageAfterHeaders(): void
    {
        $headers = "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n";
        $this->connection->httpHeadersReceived = false;
        $this->server->onMessage($this->connection, $headers);

        $message = 'Hello World!';
        $this->app->expects($this->once())->method('onMessage')->with($this->isExpectedConnection(), $message);
        $this->server->onMessage($this->connection, $message);
    }

    public function testBufferOverflow(): void
    {
        $this->connection->expects($this->once())->method('close');
        $this->connection->httpHeadersReceived = false;

        $this->server->onMessage($this->connection, str_repeat('a', 5000));
    }

    public function testCloseIfNotEstablished(): void
    {
        $this->connection->httpHeadersReceived = false;
        $this->connection->expects($this->once())->method('close');
        $this->server->onError($this->connection, new \Exception('Whoops!'));
    }

    public function testBufferHeaders(): void
    {
        $this->connection->httpHeadersReceived = false;
        $this->app->expects($this->never())->method('onOpen');
        $this->app->expects($this->never())->method('onMessage');

        $this->server->onMessage($this->connection, 'GET / HTTP/1.1');
    }
}
