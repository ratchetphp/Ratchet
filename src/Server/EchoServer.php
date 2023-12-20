<?php

namespace Ratchet\Server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class EchoServer implements MessageComponentInterface
{
    public function onOpen(ConnectionInterface $connection): void
    {
    }

    public function onMessage(ConnectionInterface $connection, string $message): void
    {
        $connection->send($message);
    }

    public function onClose(ConnectionInterface $connection): void
    {
    }

    public function onError(ConnectionInterface $connection, \Exception $exception): void
    {
        $connection->close();
    }
}
