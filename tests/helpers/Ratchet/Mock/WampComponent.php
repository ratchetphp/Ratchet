<?php

namespace Ratchet\Mock;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\WebSocket\WsServerInterface;

class WampComponent implements WampServerInterface, WsServerInterface
{
    public array $last = [];

    public array $protocols = [];

    public function getSubProtocols(): array
    {
        return $this->protocols;
    }

    public function onCall(ConnectionInterface $connection, $id, $procURI, array $params)
    {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onSubscribe(ConnectionInterface $connection, $topic)
    {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onUnSubscribe(ConnectionInterface $connection, $topic)
    {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onPublish(ConnectionInterface $connection, $topic, $event, array $exclude, array $eligible)
    {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onOpen(ConnectionInterface $connection)
    {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onClose(ConnectionInterface $connection)
    {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onError(ConnectionInterface $connection, \Exception $exception)
    {
        $this->last[__FUNCTION__] = func_get_args();
    }
}
