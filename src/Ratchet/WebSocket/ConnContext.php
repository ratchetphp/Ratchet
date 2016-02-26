<?php
namespace Ratchet\WebSocket;
use Ratchet\RFC6455\Messaging\MessageBuffer;

class ConnContext {
    /**
     * @var \Ratchet\WebSocket\WsConnection
     */
    public $connection;

    /**
     * @var \Ratchet\RFC6455\Messaging\MessageBuffer;
     */
    public $streamer;

    public function __construct(WsConnection $conn, MessageBuffer $streamer) {
        $this->connection = $conn;
        $this->streamer   = $streamer;
    }
}
