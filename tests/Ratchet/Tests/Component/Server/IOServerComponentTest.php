<?php
namespace Ratchet\Tests\Application\Server;
use Ratchet\Component\Server\IOServerComponent;
use Ratchet\Tests\Mock\FakeSocket as Socket;
use Ratchet\Tests\Mock\Component as TestApp;

/**
 * @covers Ratchet\Component\Server\IOServerComponent
 */
class IOServerComponentTest extends \PHPUnit_Framework_TestCase {
    protected $_catalyst;
    protected $_server;
    protected $_decorated;

    public function setUp() {
        $this->_catalyst  = new Socket;
        $this->_decorated = new TestApp;
        $this->_server    = new IOServerComponent($this->_decorated);

        $ref  = new \ReflectionClass('\\Ratchet\\Component\\Server\\IOServerComponent');
        $prop = $ref->getProperty('_run');
        $prop->setAccessible(true);
        $prop->setValue($this->_server, false);
    }

    protected function getPrivateProperty($class, $name) {
        $reflectedClass = new \ReflectionClass($class);
        $property = $reflectedClass->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue($class);
    }

    protected function getMasterConnection() {
        $connections = $this->getPrivateProperty($this->_server, '_connections');
        return array_pop($connections);
    }

    public function testOnOpenPassesClonedSocket() {
        $this->_server->run($this->_catalyst);
        $master = $this->getMasterConnection();

        $this->_server->onOpen($master);
        $clone = $this->_decorated->_conn_open;

        $this->assertEquals($master->getID() + 1, $clone->getID());
    }

    public function testOnMessageSendsToApp() {
        $this->_server->run($this->_catalyst);
        $master = $this->getMasterConnection();

        // todo, make FakeSocket better, set data in select, recv to pass data when called, then do this check
        // that way can mimic the TCP fragmentation/buffer situation

        $this->_server->onOpen($master);
        $clone = $this->_decorated->_conn_open;

        // $this->_server->run($this->_catalyst);
        $msg = 'Hello World!';
        $this->_server->onMessage($clone, $msg);

        $this->assertEquals($msg, $this->_decorated->_msg_recv);
    }
}