<?php
namespace Ratchet\Tests\Mock;
use Ratchet\Component\WAMP\WAMPServerComponentInterface;
use Ratchet\Resource\ConnectionInterface;

class WAMPComponent implements WAMPServerComponentInterface {
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