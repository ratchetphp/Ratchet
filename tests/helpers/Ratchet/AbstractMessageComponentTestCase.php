<?php
namespace Ratchet;

use PHPUnit\Framework\TestCase;

abstract class AbstractMessageComponentTestCase extends TestCase {
    protected $_app;
    protected $_serv;
    protected $_conn;

    abstract public function getConnectionClassString();
    abstract public function getDecoratorClassString();
    abstract public function getComponentClassString();

    /**
     * @before
     */
    public function setUpConnection() {
        $this->_app  = $this->getMockBuilder($this->getComponentClassString())->getMock();
        $decorator   = $this->getDecoratorClassString();
        $this->_serv = new $decorator($this->_app);
        $this->_conn = $this->getMockBuilder('Ratchet\Mock\Connection')->getMock();

        $this->doOpen($this->_conn);
    }

    protected function doOpen($conn) {
        $this->_serv->onOpen($conn);
    }

    public function isExpectedConnection() {
        return $this->isInstanceOf($this->getConnectionClassString());
    }

    public function testOpen() {
        $this->_app->expects($this->once())->method('onOpen')->with($this->isExpectedConnection());
        $this->doOpen($this->getMockBuilder('Ratchet\Mock\Connection')->getMock());
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
