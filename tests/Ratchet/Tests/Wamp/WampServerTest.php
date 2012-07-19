<?php
namespace Ratchet\Tests\Wamp;
use Ratchet\Wamp\WampServer;

/**
 * @covers Ratchet\Wamp\WampServer
 */
class WampServerTest extends \PHPUnit_Framework_TestCase {
    private $serv;
    private $mock;
    private $conn;

    public function setUp() {
        $this->mock = $this->getMock('\\Ratchet\\Wamp\\WampServerInterface');
        $this->serv = new WampServer($this->mock);
        $this->conn = $this->getMock('\\Ratchet\\ConnectionInterface');

        $this->serv->onOpen($this->conn);
    }

    public function isWampConn() {
        return new \PHPUnit_Framework_Constraint_IsInstanceOf('\\Ratchet\\Wamp\\WampConnection');
    }

    public function testOpen() {
        $this->mock->expects($this->once())->method('onOpen')->with($this->isWampConn());
        $this->serv->onOpen($this->getMock('\\Ratchet\\ConnectionInterface'));
    }

    public function testOnClose() {
        $this->mock->expects($this->once())->method('onClose')->with($this->isWampConn());
        $this->serv->onClose($this->conn);
    }

    public function testOnError() {
        $e = new \Exception('hurr hurr');
        $this->mock->expects($this->once())->method('onError')->with($this->isWampConn(), $e);
        $this->serv->onError($this->conn, $e);
    }

    public function testOnMessageToEvent() {
        $published = 'Client published this message';

        $this->mock->expects($this->once())->method('onPublish')->with(
            $this->isWampConn()
          , new \PHPUnit_Framework_Constraint_IsInstanceOf('\\Ratchet\\Wamp\\Topic')
          , $published
          , array()
          , array()
        );

        $this->serv->onMessage($this->conn, json_encode(array(7, 'topic', $published)));
    }

    public function testGetSubProtocols() {
        // todo: could expand on this
        $this->assertInternalType('array', $this->serv->getSubProtocols());
    }
}