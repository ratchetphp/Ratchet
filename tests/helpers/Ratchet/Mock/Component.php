<?php

namespace Ratchet\Mock;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;

class Component implements MessageComponentInterface, WsServerInterface {
    public $last = [];

    public $protocols = [];

    public function __construct(ComponentInterface $app = null) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    #[\Override]
    public function onOpen(ConnectionInterface $conn) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    #[\Override]
    public function onMessage(ConnectionInterface $from, $msg) {
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

    #[\Override]
    public function getSubProtocols() {
        return $this->protocols;
    }
}
