<?php
namespace Ratchet;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;
use Ratchet\Wamp\WampServerInterface;

class NullComponent implements MessageComponentInterface, WsServerInterface, WampServerInterface {
    public function onOpen(ConnectionInterface $conn) {}

    public function onMessage(ConnectionInterface $conn, $msg) {}

    public function onClose(ConnectionInterface $conn) {}

    public function onError(ConnectionInterface $conn, \Exception $e) {}

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {}

    public function onSubscribe(ConnectionInterface $conn, $topic) {}

    public function onUnSubscribe(ConnectionInterface $conn, $topic) {}

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude = array(), array $eligible = array()) {}

    public function getSubProtocols() {
        return array();
    }
}
