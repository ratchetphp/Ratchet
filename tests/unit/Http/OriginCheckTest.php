<?php

namespace Ratchet\Http;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\RequestInterface;
use Ratchet\AbstractMessageComponentTestCase;
use Ratchet\ConnectionInterface;

/**
 * @covers Ratchet\Http\OriginCheck
 */
class OriginCheckTest extends AbstractMessageComponentTestCase
{
    protected MockObject $requestStub;

    public function setUp(): void
    {
        $this->requestStub = $this->createMock(RequestInterface::class);
        $this->requestStub->expects($this->any())->method('getHeader')->willReturn(['localhost']);

        parent::setUp();
        $this->server->allowedOrigins[] = 'localhost';
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
    }

    public function testOnMessage(): void
    {
        $this->passthroughMessageTest('Hello World!');
    }
}
