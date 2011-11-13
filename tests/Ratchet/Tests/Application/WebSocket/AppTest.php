<?php
namespace Ratchet\Tests\Application\WebSocket;
use Ratchet\Application\WebSocket\App as RealApp;
use Ratchet\Tests\Mock\Socket;
use Ratchet\Tests\Mock\Application;

/**
 * @covers Ratchet\Application\WebSocket\App
 */
class AppTest extends \PHPUnit_Framework_TestCase {
    protected $_ws;

    public function setUp() {
        $this->_ws = new RealApp(new Application);
    }

    public function testGetConfigReturnsArray() {
        $this->assertInternalType('array', $this->_ws->getDefaultConfig());
    }
}