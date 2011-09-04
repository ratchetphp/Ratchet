<?php
namespace Ratchet\Tests;
use Ratchet\Server;
use Ratchet\Tests\Mock\Socket;

/**
 * @covers Ratchet\Server
 */
class ServerTest extends \PHPUnit_Framework_TestCase {
    protected $_server;

    public function setUp() {
        $this->_server = new Server(new Socket());
    }

    public function testServerHasServerInterface() {
        $constraint = $this->isInstanceOf('\\Ratchet\\ServerInterface');
        $this->assertThat($this->_server, $constraint);
    }
}