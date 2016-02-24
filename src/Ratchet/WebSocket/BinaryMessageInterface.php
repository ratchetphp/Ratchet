<?php
namespace Ratchet\WebSocket;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

interface BinaryMessageInterface extends MessageComponentInterface {
    public function onMessage(ConnectionInterface $conn, $msg, $isBinary = false);
}
