<?php
namespace Ratchet\Server;
use Ratchet\ConnectionInterface;
use React\Socket\ConnectionInterface as SocketConnection;

/**
 * {@inheritdoc}
 */
#[\AllowDynamicProperties]
class IoConnection implements ConnectionInterface {
    /**
     * @var \React\Socket\ConnectionInterface
     */
    protected $conn;


    /**
     * @param \React\Socket\ConnectionInterface $conn
     */
    public function __construct(SocketConnection $conn) {
        $this->conn = $conn;
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
}
