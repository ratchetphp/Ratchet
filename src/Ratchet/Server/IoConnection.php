<?php
namespace Ratchet\Server;
use Ratchet\ConnectionInterface;
use Ratchet\IdentifiedConnectionInterface;
use React\Socket\ConnectionInterface as ReactConn;

/**
 * {@inheritdoc}
 */
class IoConnection implements IdentifiedConnectionInterface {
    /**
     * @var \React\Socket\ConnectionInterface
     */
    protected $conn;

    /**
     * @var int
     */
    private $id;

    /**
     * @param \React\Socket\ConnectionInterface $conn
     * @param int the connection identifier
     */
    public function __construct(ReactConn $conn, $id) {
        $this->conn = $conn;
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function send($data) {
        $this->conn->write($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function close() {
        $this->conn->end();
    }

    /**
     * {@inheritDoc}
     */
    public function getId() {
        return $this->id;
    }
}
