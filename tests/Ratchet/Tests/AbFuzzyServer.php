<?php
namespace Ratchet\Tests;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class AbFuzzyServer implements MessageComponentInterface {
    public function onOpen(ConnectionInterface $conn) {
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $from->send($msg);
    }

    public function onClose(ConnectionInterface $conn) {
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo $e->getMessage() . "\n";

        $conn->close();
    }
}