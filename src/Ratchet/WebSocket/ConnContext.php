<?php

namespace Ratchet\WebSocket;
use Ratchet\RFC6455\Messaging\MessageBuffer;

class ConnContext {
    /**
     * @var \Ratchet\WebSocket\WsConnection
     */
    public $connection;

    public function __construct(
        WsConnection $conn,
        public MessageBuffer $buffer
    ) {
        $this->connection = $conn;
    }
}
