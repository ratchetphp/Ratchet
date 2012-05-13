<?php
namespace Ratchet\Tests\Mock;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\WebSocket\WsServerInterface;
use Ratchet\ConnectionInterface;

class WampComponent implements WampServerInterface, WsServerInterface {
    public $last = array();

    public $protocols = array();

    public function getSubProtocols() {
        return $this->protocols;
    }

    public function onCall(ConnectionInterface $conn, $id, $procURI, array $params) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onSubscribe(ConnectionInterface $conn, $uri) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onUnSubscribe(ConnectionInterface $conn, $uri) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onPublish(ConnectionInterface $conn, $uri, $event, $exclude, $eligible) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onClose(ConnectionInterface $conn) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->last[__FUNCTION__] = func_get_args();
    }
}