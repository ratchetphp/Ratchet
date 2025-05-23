<?php
namespace Ratchet;

/**
 * Wraps ConnectionInterface objects via the decorator pattern but allows
 * parameters to bubble through with magic methods
 *
 * Note that this instance does not use the `#[\AllowDynamicProperties]`
 * attribute for PHP 8.2+ compatibility as any properties added to this class
 * will be forwarded to the wrapped instance.
 *
 * @todo It sure would be nice if I could make most of this a trait...
 */
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
