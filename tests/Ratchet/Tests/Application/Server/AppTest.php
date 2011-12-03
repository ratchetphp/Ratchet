<?php
namespace Ratchet\Tests\Application\Server;
use Ratchet\Application\Server\App as ServerApp;
use Ratchet\Tests\Mock\FakeSocket as Socket;
use Ratchet\Tests\Mock\Application as TestApp;

/**
 * @covers Ratchet\Application\Server\App
 */
class AppTest extends \PHPUnit_Framework_TestCase {
    protected $_catalyst;
    protected $_server;
    protected $_app;

    public function setUp() {
        $this->_catalyst = new Socket;
        $this->_app      = new TestApp;
        $this->_server   = new ServerApp($this->_app);

        $ref  = new \ReflectionClass('\Ratchet\Application\Server\App');
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

    public function testDoNotAllowStacklessServer() {
        $this->setExpectedException('UnexpectedValueException');
        new ServerApp;
    }

    public function testOnOpenPassesClonedSocket() {
        $this->_server->run($this->_catalyst);
        $master = $this->getMasterConnection();

        $this->_server->onOpen($master);
        $clone = $this->_app->_conn_open;

        $this->assertEquals($master->getID() + 1, $clone->getID());
    }

    public function testOnMessageSendsToApp() {
        $this->_server->run($this->_catalyst);
        $master = $this->getMasterConnection();

        // todo, make FakeSocket better, set data in select, recv to pass data when called, then do this check
        // that way can mimic the TCP fragmentation/buffer situation

        $this->_server->onOpen($master);
        $clone = $this->_app->_conn_open;

        // $this->_server->run($this->_catalyst);
        $msg = 'Hello World!';
        $this->_server->onMessage($clone, $msg);

        $this->assertEquals($msg, $this->_app->_msg_recv);
    }
}