<?php
namespace Ratchet\Tests\Protocol\WebSocket;
use Ratchet\Server;
use Ratchet\Protocol\WebSocket\Server as WebServer;
use Ratchet\Tests\Mock\Socket;

/**
 * @covers Ratchet\Protocol\WebSocket\Server
 */
class ServerTest extends \PHPUnit_Framework_TestCase {
    protected $_server;

    public function setUp() {
        $this->_server = new WebServer(new Server(new Socket()));
    }

    public function testServerImplementsServerInterface() {
        $constraint = $this->isInstanceOf('\\Ratchet\\ServerInterface');
        $this->assertThat($this->_server, $constraint);
    }

    public function testServerImplementsProtocolInterface() {
        $constraint = $this->isInstanceOf('\\Ratchet\\Protocol\ProtocolInterface');
        $this->assertThat($this->_server, $constraint);
    }
}