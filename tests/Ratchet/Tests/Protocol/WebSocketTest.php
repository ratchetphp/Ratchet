<?php
namespace Ratchet\Tests\Protocol;
use Ratchet\Application\WebSocket\App as WebSocket;
use Ratchet\Tests\Mock\Socket;
use Ratchet\Tests\Mock\Application;

/**
 * @covers Ratchet\Application\WebSocket
 */
class WebSocketTest extends \PHPUnit_Framework_TestCase {
    protected $_ws;

    public function setUp() {
        $this->_ws = new WebSocket(new Application);
    }

    public function testGetConfigReturnsArray() {
        $this->assertInternalType('array', $this->_ws->getDefaultConfig());
    }
}