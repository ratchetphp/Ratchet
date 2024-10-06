<?php

namespace Ratchet;

use Psr\Http\Message\RequestInterface;

/**
 * Wraps ConnectionInterface objects via the decorator pattern but allows
 * parameters to bubble through with magic methods
 * @todo It sure would be nice if I could make most of this a trait...
 */
abstract class AbstractConnectionDecorator implements ConnectionInterface {
    public RequestInterface $httpRequest;

    protected \stdClass $WebSocket;

    public function __construct(
        protected ConnectionInterface $wrappedConn
    ) {
    }

    /**
     * @return ConnectionInterface
     */
    protected function getConnection(): ConnectionInterface
    {
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
