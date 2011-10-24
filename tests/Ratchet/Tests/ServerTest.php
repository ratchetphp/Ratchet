<?php
namespace Ratchet\Tests;
use Ratchet\Server;
use Ratchet\Tests\Mock\FakeSocket as Socket;
use Ratchet\Tests\Mock\Application as TestApp;

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

    public function testServerCanNotRunWithoutApplication() {
        $this->setExpectedException('\\RuntimeException');
        $this->_server->run();
    }

    public function testAttatchedReceiverIsSet() {
        $app = new TestApp();

        $this->_server->attatchReceiver($app);
// todo, use proper assertions...can't look them up atm, no internet
        $this->assertAttributeEquals(Array(spl_object_hash($app) => $app), '_receivers', $this->_server);
    }

    public function testBindToInvalidAddress() {
        $app = new TestApp();

        $this->_server->attatchReceiver($app);
        $this->setExpectedException('\\Ratchet\\Exception');

        $this->_server->run('la la la', 80);
    }
}