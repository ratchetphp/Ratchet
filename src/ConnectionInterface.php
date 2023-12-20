<?php

namespace Ratchet;

/**
 * A proxy object representing a connection to the application
 * This acts as a container to store data (in memory) about the connection
 */
interface ConnectionInterface
{
    /**
     * Send data to the connection
     */
    public function send(string $data): ConnectionInterface;

    /**
     * Close the connection
     */
    public function close();
}
