<?php

namespace Ratchet\Wamp;

use PHPUnit\Framework\Constraint\IsInstanceOf;
use Ratchet\AbstractMessageComponentTestCase;

/**
 * @covers Ratchet\Wamp\WampServer
 */
class WampServerTest extends AbstractMessageComponentTestCase
{
    public function getConnectionClassString(): string
    {
        return WampConnection::class;
    }

    public function getDecoratorClassString(): string
    {
        return WampServer::class;
    }

    public function getComponentClassString(): string
    {
        return WampServerInterface::class;
    }

    public function testOnMessageToEvent(): void
    {
        $published = 'Client published this message';

        $this->app->expects($this->once())->method('onPublish')->with(
            $this->isExpectedConnection(),
            new IsInstanceOf(Topic::class),
            $published,
            [],
            []
        );

        $this->server->onMessage($this->connection, json_encode([7, 'topic', $published]));
    }

    public function testGetSubProtocols(): void
    {
        // todo: could expand on this
        $this->assertIsArray($this->server->getSubProtocols());
    }

    public function testConnectionClosesOnInvalidJson(): void
    {
        $this->connection->expects($this->once())->method('close');
        $this->server->onMessage($this->connection, 'invalid json');
    }

    public function testConnectionClosesOnProtocolError(): void
    {
        $this->connection->expects($this->once())->method('close');
        $this->server->onMessage($this->connection, json_encode(['valid' => 'json', 'invalid' => 'protocol']));
    }
}
