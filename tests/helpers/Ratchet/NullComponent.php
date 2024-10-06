<?php

namespace Ratchet;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\WebSocket\WsServerInterface;

class NullComponent implements MessageComponentInterface, WsServerInterface, WampServerInterface {
    #[\Override]
    public function onOpen(ConnectionInterface $conn) {}

    #[\Override]
    public function onMessage(ConnectionInterface $conn, $msg) {}

    #[\Override]
    public function onClose(ConnectionInterface $conn) {}

    #[\Override]
    public function onError(ConnectionInterface $conn, \Exception $e) {}

    #[\Override]
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {}

    #[\Override]
    public function onSubscribe(ConnectionInterface $conn, $topic) {}

    #[\Override]
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {}

    #[\Override]
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude = [], array $eligible = []) {}

    #[\Override]
    public function getSubProtocols() {
        return [];
    }
}
