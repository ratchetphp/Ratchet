<?php
namespace Ratchet\Server;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\Connection as ReactConn;

/**
 * {@inheritdoc}
 */
class IoConnection implements ConnectionInterface {
    /**
     * @var \React\Socket\ConnectionInterface
     */
    protected $conn;
	/**
	 * Explicitly define properties to prevent dynamic property deprecation notices
	 */
	public $resourceId;
	public $remoteAddress;
	public $httpHeadersReceived;
	public $httpBuffer;
	public $httpRequest;
	public $WebSocket;

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
