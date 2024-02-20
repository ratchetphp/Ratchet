<?php

namespace Ratchet\Server;

use Ratchet\ConnectionInterface;
use React\Socket\ConnectionInterface as ReactConn;

/**
 * {@inheritdoc}
 */
class IoConnection implements ConnectionInterface
{
    /**
     * @var \React\Socket\ConnectionInterface
     */
    protected $conn;

    protected $properties = [];

    public function __set($key, $value)
    {
        if (!property_exists($this, $key)) {
            $this->properties[$key] = $value;
        }
    }

    public function __get($key)
    {
        if (isset($this->properties[$key])) {
            return $this->properties[$key];
        }
    }

    /**
     * @param \React\Socket\ConnectionInterface $conn
     */
    public function __construct(ReactConn $conn)
    {
        $this->conn = $conn;
    }

    /**
     * {@inheritdoc}
     */
    public function send($data)
    {
        $this->conn->write($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->conn->end();
    }
}
