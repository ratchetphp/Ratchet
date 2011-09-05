<?php
namespace Ratchet\Tests\Protocol;
use Ratchet\Protocol\WebSocket;
use Ratchet\Tests\Mock\Socket;

/**
 * @covers Ratchet\Protocol\WebSocket
 */
class ServerTest extends \PHPUnit_Framework_TestCase {
    protected $_ws;

    public function setUp() {
        $this->_ws = new WebSocket();
    }

    public function testServerImplementsServerInterface() {
        $constraint = $this->isInstanceOf('\\Ratchet\\ReceiverInterface');
        $this->assertThat($this->_ws, $constraint);
    }

    public function testServerImplementsProtocolInterface() {
        $constraint = $this->isInstanceOf('\\Ratchet\\Protocol\ProtocolInterface');
        $this->assertThat($this->_ws, $constraint);
    }

    public function testGetConfigReturnsArray() {
        $this->assertInternalType('array', $this->_ws->getDefaultConfig());
    }
}