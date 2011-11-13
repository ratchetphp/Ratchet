<?php
namespace Ratchet\Tests;
use Ratchet\Application\Server\App as Server;
use Ratchet\Tests\Mock\FakeSocket as Socket;
use Ratchet\Tests\Mock\Application as TestApp;

/**
 * @covers Ratchet\Server\App
 */
class AppTest extends \PHPUnit_Framework_TestCase {
    protected $_catalyst;
    protected $_server;
    protected $_app;

    public function setUp() {
        $this->_catalyst = new Socket;
        $this->_app      = new TestApp;
        $this->_server   = new Server($this->_app);
    }

    protected function getPrivateProperty($class, $name) {
        $reflectedClass = new \ReflectionClass($class);
        $property = $reflectedClass->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue($class);
    }

    public function testBindToInvalidAddress() {
        return $this->markTestIncomplete();

        $app = new TestApp();

        $this->_server->attatchReceiver($app);
        $this->setExpectedException('\\Ratchet\\Exception');

        $this->_server->run('la la la', 80);
    }
}