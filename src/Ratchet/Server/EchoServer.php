<?php

namespace Ratchet\Server;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * A simple Ratchet application that will reply to all messages with the message it received
 */
class EchoServer implements MessageComponentInterface {
    #[\Override]
    public function onOpen(ConnectionInterface $conn) {
    }

    #[\Override]
    public function onMessage(ConnectionInterface $from, $msg) {
        $from->send($msg);
    }

    #[\Override]
    public function onClose(ConnectionInterface $conn) {
    }

    #[\Override]
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }
}
