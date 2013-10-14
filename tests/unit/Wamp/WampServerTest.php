<?php
namespace Ratchet\Wamp;
use Ratchet\Wamp\WampServer;
use Ratchet\AbstractMessageComponentTestCase;

/**
 * @covers Ratchet\Wamp\WampServer
 */
class WampServerTest extends AbstractMessageComponentTestCase {
    public function getConnectionClassString() {
        return '\Ratchet\Wamp\WampConnection';
    }

    public function getDecoratorClassString() {
        return 'Ratchet\Wamp\WampServer';
    }

    public function getComponentClassString() {
        return '\Ratchet\Wamp\WampServerInterface';
    }

    public function testOnMessageToEvent() {
        $published = 'Client published this message';

        $this->_app->expects($this->once())->method('onPublish')->with(
            $this->isExpectedConnection()
          , new \PHPUnit_Framework_Constraint_IsInstanceOf('\Ratchet\Wamp\Topic')
          , $published
          , array()
          , array()
        );

        $this->_serv->onMessage($this->_conn, json_encode(array(7, 'topic', $published)));
    }

    public function testGetSubProtocols() {
        // todo: could expand on this
        $this->assertInternalType('array', $this->_serv->getSubProtocols());
    }

    public function testConnectionClosesOnInvalidJson() {
        $this->_conn->expects($this->once())->method('close');
        $this->_serv->onMessage($this->_conn, 'invalid json');
    }

    public function testConnectionClosesOnProtocolError() {
        $this->_conn->expects($this->once())->method('close');
        $this->_serv->onMessage($this->_conn, json_encode(array('valid' => 'json', 'invalid' => 'protocol')));
    }
}