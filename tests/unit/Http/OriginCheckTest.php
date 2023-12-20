<?php

namespace Ratchet\Http;

use Psr\Http\Message\RequestInterface;
use Ratchet\AbstractMessageComponentTestCase;
use Ratchet\ConnectionInterface;

/**
 * @covers Ratchet\Http\OriginCheck
 */
class OriginCheckTest extends AbstractMessageComponentTestCase
{
    protected $requestStub;

    public function setUp(): void
    {
        $this->requestStub = $this->getMockBuilder(RequestInterface::class)->getMock();
        $this->requestStub->expects($this->any())->method('getHeader')->will($this->returnValue(['localhost']));

        parent::setUp();
        $this->server->allowedOrigins[] = 'localhost';
    }

    protected function doOpen(ConnectionInterface $connection): void
    {
        $this->server->onOpen($connection, $this->requestStub);
    }

    public function getConnectionClassString(): string
    {
        return ConnectionInterface::class;
    }

    public function getDecoratorClassString(): string
    {
        return OriginCheck::class;
    }

    public function getComponentClassString(): string
    {
        return HttpServerInterface::class;
    }

    public function testCloseOnNonMatchingOrigin(): void
    {
        $this->server->allowedOrigins = ['socketo.me'];
        $this->connection->expects($this->once())->method('close');
        $this->server->onOpen($this->connection, $this->requestStub);
    }

    public function testOnMessage(): void
    {
        $this->passthroughMessageTest('Hello World!');
    }
}
