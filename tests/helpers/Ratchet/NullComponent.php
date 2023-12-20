<?php

namespace Ratchet;

use Ratchet\Wamp\WampServerInterface;
use Ratchet\WebSocket\WsServerInterface;

class NullComponent implements MessageComponentInterface, WampServerInterface, WsServerInterface
{
    public function onOpen(ConnectionInterface $connection)
    {
    }

    public function onMessage(ConnectionInterface $connection, string $message)
    {
    }

    public function onClose(ConnectionInterface $connection)
    {
    }

    public function onError(ConnectionInterface $connection, \Exception $exception)
    {
    }

    public function onCall(ConnectionInterface $connection, $id, $topic, array $params)
    {
    }

    public function onSubscribe(ConnectionInterface $connection, $topic)
    {
    }

    public function onUnSubscribe(ConnectionInterface $connection, $topic)
    {
    }

    public function onPublish(ConnectionInterface $connection, $topic, $event, array $exclude = [], array $eligible = [])
    {
    }

    public function getSubProtocols(): array
    {
        return [];
    }
}
