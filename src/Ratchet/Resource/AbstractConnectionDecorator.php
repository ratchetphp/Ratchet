<?php
namespace Ratchet\Resource;

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
 
    /**
     * @todo trigger_error() instead - Have it the same as if called from a POPO
     */
    public function __get($name) {
        if (!$this->__isset($name)) {
            throw new \InvalidArgumentException("Attribute '{$name}' not found in Connection {$this->getID()}");
        }
 
        return $this->wrappedConn->$name;
    }
 
    public function __isset($name) {
        return isset($this->wrappedConn->$name);
    }
 
    public function __unset($name) {
        unset($this->wrappedConn->$name);
    }
}