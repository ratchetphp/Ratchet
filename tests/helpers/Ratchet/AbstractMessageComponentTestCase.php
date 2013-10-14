<?php
namespace Ratchet;

abstract class AbstractMessageComponentTestCase extends \PHPUnit_Framework_TestCase {
    protected $_app;
    protected $_serv;
    protected $_conn;

    abstract public function getConnectionClassString();
    abstract public function getDecoratorClassString();
    abstract public function getComponentClassString();

    public function setUp() {
        $this->_app  = $this->getMock($this->getComponentClassString());
        $decorator   = $this->getDecoratorClassString();
        $this->_serv = new $decorator($this->_app);
        $this->_conn = $this->getMock('\Ratchet\ConnectionInterface');

        $this->doOpen($this->_conn);
    }

    protected function doOpen($conn) {
        $this->_serv->onOpen($conn);
    }

    public function isExpectedConnection() {
        return new \PHPUnit_Framework_Constraint_IsInstanceOf($this->getConnectionClassString());
    }

    public function testOpen() {
        $this->_app->expects($this->once())->method('onOpen')->with($this->isExpectedConnection());
        $this->doOpen($this->getMock('\Ratchet\ConnectionInterface'));
    }

    public function testOnClose() {
        $this->_app->expects($this->once())->method('onClose')->with($this->isExpectedConnection());
        $this->_serv->onClose($this->_conn);
    }

    public function testOnError() {
        $e = new \Exception('Whoops!');
        $this->_app->expects($this->once())->method('onError')->with($this->isExpectedConnection(), $e);
        $this->_serv->onError($this->_conn, $e);
    }

    public function passthroughMessageTest($value) {
        $this->_app->expects($this->once())->method('onMessage')->with($this->isExpectedConnection(), $value);
        $this->_serv->onMessage($this->_conn, $value);
    }
}