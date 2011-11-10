<?php
namespace Ratchet\Tests;
use Ratchet\Server;
use Ratchet\Tests\Mock\FakeSocket as Socket;
use Ratchet\Tests\Mock\Application as TestApp;

/**
 * @covers Ratchet\Server
 */
class ServerTest extends \PHPUnit_Framework_TestCase {
    protected $_catalyst;
    protected $_server;
    protected $_app;

    public function setUp() {
        $this->_catalyst = new Socket;
        $this->_app      = new TestApp;
        $this->_server   = new Server($this->_catalyst, $this->_app);
    }

    protected function getPrivateProperty($class, $name) {
        $reflectedClass = new \ReflectionClass($class);
        $property = $reflectedClass->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue($class);
    }

    public function testServerHasServerInterface() {
        $constraint = $this->isInstanceOf('\\Ratchet\\SocketObserver');
        $this->assertThat($this->_server, $constraint);
    }

    public function testIteration() {
        $this->assertInstanceOf('\\Iterator', $this->_server->getIterator());
    }

    public function SKIPtestServerCanNotRunWithoutApplication() {
        $this->setExpectedException('\\RuntimeException');
        $this->_server->run();
    }

/*
    public function testAttatchedReceiverIsSet() {
        $app = new TestApp();

        $this->_server->attatchReceiver($app);
// todo, use proper assertions...can't look them up atm, no internet
        $this->assertAttributeEquals(Array(spl_object_hash($app) => $app), '_receivers', $this->_server);
    }

/**/
    public function testBindToInvalidAddress() {
        return $this->markTestIncomplete();

        $app = new TestApp();

        $this->_server->attatchReceiver($app);
        $this->setExpectedException('\\Ratchet\\Exception');

        $this->_server->run('la la la', 80);
    }
}