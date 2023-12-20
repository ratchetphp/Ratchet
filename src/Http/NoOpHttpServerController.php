<?php

namespace Ratchet\Http;

use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;

class NoOpHttpServerController implements HttpServerInterface
{
    public function onOpen(ConnectionInterface $connection, ?RequestInterface $request = null)
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
}
