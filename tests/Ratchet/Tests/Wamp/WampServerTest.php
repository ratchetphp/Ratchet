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
    }

    public function isWampConn() {
        return new \PHPUnit_Framework_Constraint_IsInstanceOf('\\Ratchet\\Wamp\\WampConnection');
    }

    public function testOpen() {
        $this->mock->expects($this->once())->method('onOpen')->with($this->isWampConn());
        $this->serv->onOpen($this->conn);
    }
}
