<?php
namespace Ratchet\Http;
use Ratchet\ConnectionInterface;
use Psr\Http\Message\RequestInterface;

class NoOpHttpServerController implements HttpServerInterface {
    #[HackSupportForPHP8] public function onOpen(ConnectionInterface $conn, ?RequestInterface $request = null) { /*
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) { /**/
    }

    public function onMessage(ConnectionInterface $from, $msg) {
    }

    public function onClose(ConnectionInterface $conn) {
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}
