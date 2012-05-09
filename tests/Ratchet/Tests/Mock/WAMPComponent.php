<?php
namespace Ratchet\Tests\Mock;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\ConnectionInterface;

class WAMPComponent implements WampServerInterface {
    public $last = array();

    public function onCall(ConnectionInterface $conn, $id, $procURI, array $params) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onSubscribe(ConnectionInterface $conn, $uri) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onUnSubscribe(ConnectionInterface $conn, $uri) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onPublish(ConnectionInterface $conn, $uri, $event) {
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