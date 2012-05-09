<?php
namespace Ratchet;

abstract class AbstractConnectionDecorator implements ConnectionInterface {
    /**
     * @var ConnectionInterface
     */
    protected $wrappedConn;

    public function __construct(ConnectionInterface $conn) {
        $this->wrappedConn = $conn;
    }

    /**
     * @return ConnectionInterface
     */
    protected function getConnection() {
        return $this->wrappedConn;
    }

    public function __set($name, $value) {
        $this->wrappedConn->$name = $value;
    }
 
    public function __get($name) {
        return $this->wrappedConn->$name;
    }
 
    public function __isset($name) {
        return isset($this->wrappedConn->$name);
    }
 
    public function __unset($name) {
        unset($this->wrappedConn->$name);
    }
}