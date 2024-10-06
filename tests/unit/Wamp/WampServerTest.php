<?php

namespace Ratchet\Wamp;
use Ratchet\AbstractMessageComponentTestCase;

/**
 * @covers Ratchet\Wamp\WampServer
 */
class WampServerTest extends AbstractMessageComponentTestCase {
    #[\Override]
    public function getConnectionClassString(): string {
        return \Ratchet\Wamp\WampConnection::class;
    }

    #[\Override]
    public function getDecoratorClassString(): string {
        return \Ratchet\Wamp\WampServer::class;
    }

    #[\Override]
    public function getComponentClassString(): string {
        return \Ratchet\Wamp\WampServerInterface::class;
    }

    public function testOnMessageToEvent(): void {
        $published = 'Client published this message';

        $this->_app->expects($this->once())->method('onPublish')->with(
            $this->isExpectedConnection(),
            new \PHPUnit_Framework_Constraint_IsInstanceOf(\Ratchet\Wamp\Topic::class),
            $published,
            [],
            []
        );

        $this->_serv->onMessage($this->_conn, json_encode([7, 'topic', $published]));
    }

    public function testGetSubProtocols(): void {
        // todo: could expand on this
        $this->assertInternalType('array', $this->_serv->getSubProtocols());
    }

    public function testConnectionClosesOnInvalidJson(): void {
        $this->_conn->expects($this->once())->method('close');
        $this->_serv->onMessage($this->_conn, 'invalid json');
    }

    public function testConnectionClosesOnProtocolError(): void {
        $this->_conn->expects($this->once())->method('close');
        $this->_serv->onMessage($this->_conn, json_encode([
            'valid' => 'json',
            'invalid' => 'protocol',
        ]));
    }
}
