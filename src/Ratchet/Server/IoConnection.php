<?php
namespace Ratchet\Server;
use Ratchet\ConnectionInterface;
use Ratchet\Traits\DynamicPropertiesTrait;
use React\Socket\ConnectionInterface as ReactConn;

/**
 * {@inheritdoc}
 */
class IoConnection implements ConnectionInterface {
    use DynamicPropertiesTrait;
    /**
     * @var \React\Socket\ConnectionInterface
     */
    protected $conn;

    /**
     * @param \React\Socket\ConnectionInterface $conn
     */
    public function __construct(ReactConn $conn) {
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
