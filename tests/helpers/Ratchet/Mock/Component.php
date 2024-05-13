<?php
namespace Ratchet\Mock;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;
use Ratchet\ConnectionInterface;
use Throwable;

class Component implements MessageComponentInterface, WsServerInterface {
    public $last = array();

    public $protocols = array();

    public function __construct(ComponentInterface $app = null) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onClose(ConnectionInterface $conn) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onError(ConnectionInterface $conn, Throwable $e) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function getSubProtocols() {
        return $this->protocols;
    }
}
