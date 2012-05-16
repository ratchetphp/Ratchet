<?php
namespace Ratchet\Server;
use Ratchet\ConnectionInterface;
use React\Socket\ConnectionInterface as ReactConn;

/**
 * {@inheritdoc}
 */
class IoConnection implements ConnectionInterface {
    /**
     * @var Ratchet\Server\IOServer
     */
    protected $server;

    /**
     * @var React\Socket\ConnectionInterface
     */
    protected $conn;

    public function __construct(ReactConn $conn, IoServer $server) {
        $this->conn   = $conn;
        $this->server = $server;
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