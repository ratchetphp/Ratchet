<?php
namespace Ratchet\Server;
use Ratchet\ConnectionInterface;
use React\Socket\ConnectionInterface as ReactConn;

/**
 * {@inheritdoc}
 */
class IoConnection implements ConnectionInterface {
    /**
     * @var React\Socket\ConnectionInterface
     */
    protected $conn;

    public function __construct(ReactConn $conn) {
        $this->conn   = $conn;
    }

    /**
     * {@inheritdoc}
     */
    public function send($data) {
        return $this->conn->write($data);
    }

    /**
     * {@inheritdoc}
     */
    public function close() {
        $this->conn->end();
    }
}