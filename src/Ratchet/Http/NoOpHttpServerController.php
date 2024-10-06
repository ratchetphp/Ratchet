<?php

namespace Ratchet\Http;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;

class NoOpHttpServerController implements HttpServerInterface {
    #[\Override]
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
    }

    #[\Override]
    public function onMessage(ConnectionInterface $from, $msg) {
    }

    #[\Override]
    public function onClose(ConnectionInterface $conn) {
    }

    #[\Override]
    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}
