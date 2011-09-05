<?php
namespace Ratchet\Tests;
use Ratchet\Server;
use Ratchet\Tests\Mock\Socket;
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

    public function testAttatchedApplicationIsSet() {
        $app = new TestApp();

        $this->_server->attatchApplication($app);
        $this->assertAttributeEquals($app, '_app', $this->_server);
    }
}