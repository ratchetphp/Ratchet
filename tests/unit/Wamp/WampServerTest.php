<?php
namespace Ratchet\Wamp;
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

        if ($this->_version() < 6) {
            $topic = new \PHPUnit_Framework_Constraint_IsInstanceOf('\Ratchet\Wamp\Topic');
        } else {
            $topic = new \PHPUnit\Framework\Constraint\IsInstanceOf('\Ratchet\Wamp\Topic');
        }

        $this->_app->expects($this->once())->method('onPublish')->with(
            $this->isExpectedConnection()
          , $topic
          , $published
          , array()
          , array()
        );

        $this->_serv->onMessage($this->_conn, json_encode(array(7, 'topic', $published)));
    }

    public function testGetSubProtocols() {
        // todo: could expand on this
        if ($this->_version() < 7.5) {
            $this->assertInternalType('array', $this->_serv->getSubProtocols());
        } else {
            $this->assertIsArray($this->_serv->getSubProtocols());
        }
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
