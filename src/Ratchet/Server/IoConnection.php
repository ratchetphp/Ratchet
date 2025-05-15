<?php
namespace Ratchet\Server;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\ReactConnection;

/**
 * {@inheritdoc}
 */
class IoConnection implements ConnectionInterface {
    /**
     * @var ReactConnection
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
     * @param ReactConnection $conn
     */
    public function __construct(ReactConnection $conn) {
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
