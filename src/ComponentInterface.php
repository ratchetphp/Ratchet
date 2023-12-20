<?php

namespace Ratchet;

/**
 * This is the interface to build a Ratchet application with.
 * It implements the decorator pattern to build an application stack
 */
interface ComponentInterface
{
    /**
     * When a new connection is opened it will be passed to this method
     *
     * @param  ConnectionInterface  $connection The socket/connection that just connected to your application
     *
     * @throws \Exception
     */
    public function onOpen(ConnectionInterface $connection);

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $connection will not result in an error if it has already been closed.
     *
     * @param  ConnectionInterface  $connection The socket/connection that is closing/closed
     *
     * @throws \Exception
     */
    public function onClose(ConnectionInterface $connection);

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     *
     * @throws \Exception
     */
    public function onError(ConnectionInterface $connection, \Exception $exception);
}
