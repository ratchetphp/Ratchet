<?php

namespace Ratchet\Mock;

use Ratchet\ComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;

class Component implements MessageComponentInterface, WsServerInterface
{
    public array $last = [];

    public array $protocols = [];

    public function __construct(?ComponentInterface $app = null)
    {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onOpen(ConnectionInterface $connection)
    {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onMessage(ConnectionInterface $connection, string $message)
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

    public function getSubProtocols(): array
    {
        return $this->protocols;
    }
}
