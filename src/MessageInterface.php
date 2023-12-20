<?php

namespace Ratchet;

interface MessageInterface
{
    /**
     * Triggered when a client sends data through the socket
     *
     * @param  \Ratchet\ConnectionInterface  $connection The socket/connection that sent the message to your application
     * @param  string  $message  The message received
     *
     * @throws \Exception
     */
    public function onMessage(ConnectionInterface $connection, string $message);
}
