<?php
namespace Ratchet;

use Throwable;

/**
 * This is the interface to build a Ratchet application with.
 * It implements the decorator pattern to build an application stack
 */
interface ComponentInterface {
    /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws Throwable
     */
    function onOpen(ConnectionInterface $conn);

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws Throwable
     */
    function onClose(ConnectionInterface $conn);

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Throwable is thrown,
     * the Throwable is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  Throwable          $e
     * @throws Throwable
     */
    function onError(ConnectionInterface $conn, Throwable $e);
}
