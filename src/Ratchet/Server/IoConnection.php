<?php
namespace Ratchet\Server;
use Ratchet\ConnectionInterface;
use React\Socket\ConnectionInterface as ReactConn;

/**
 * {@inheritdoc}
 */
class IoConnection implements ConnectionInterface {
    /**
     * @var \React\Socket\ConnectionInterface
     */
    protected $conn;

    public $resourceId;
    public $remoteAddress;
    public $httpHeadersReceived;
    public $httpBuffer;
    public $Session;
    public $httpRequest;
    public $WebSocket;
    public $PeriodicTimer;
    public $WAMP;


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
