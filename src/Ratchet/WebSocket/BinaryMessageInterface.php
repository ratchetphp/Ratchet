<?php
namespace Ratchet\WebSocket;
use Ratchet\MessageInterface;
use Ratchet\ConnectionInterface;

interface BinaryMessageInterface extends MessageInterface {
    public function onMessage(ConnectionInterface $conn, $msg, $isBinary = false);
}
