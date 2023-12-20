<?php

namespace Ratchet\WebSocket;

use Ratchet\RFC6455\Messaging\MessageBuffer;

class ConnectionContext
{
    public function __construct(
        public WsConnection $connection,
        public MessageBuffer $buffer,
    ) {
    }
}
