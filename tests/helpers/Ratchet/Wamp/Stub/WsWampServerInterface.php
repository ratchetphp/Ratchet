<?php

namespace Ratchet\Wamp\Stub;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\WebSocket\WsServerInterface;

interface WsWampServerInterface extends WsServerInterface, WampServerInterface {
}
