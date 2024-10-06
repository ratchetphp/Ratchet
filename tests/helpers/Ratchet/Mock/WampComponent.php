<?php

namespace Ratchet\Mock;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\WebSocket\WsServerInterface;

class WampComponent implements WampServerInterface, WsServerInterface {
    public $last = [];

    public $protocols = [];

    #[\Override]
    public function getSubProtocols() {
        return $this->protocols;
    }

    #[\Override]
    public function onCall(ConnectionInterface $conn, $id, $procURI, array $params) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    #[\Override]
    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    #[\Override]
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    #[\Override]
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    #[\Override]
    public function onOpen(ConnectionInterface $conn) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    #[\Override]
    public function onClose(ConnectionInterface $conn) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    #[\Override]
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->last[__FUNCTION__] = func_get_args();
    }
}
