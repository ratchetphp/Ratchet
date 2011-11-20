<?php
namespace Ratchet;

/**
 * Observable/Observer design pattern interface for handing events on a socket
 * @todo Consider an onDisconnect method for a server-side close()'ing of a connection - onClose would be client side close()
 * @todo Is this interface needed anymore?
 * @deprecated
 */
interface ObserverInterface {
    /**
     * When a new connection is opened it will be passed to this method
     * @param SocketInterface The socket/connection that just connected to your application
     * @return Ratchet\Resource\Command\CommandInterface|null
     */
    function onOpen(SocketInterface $conn);

    /**
     * Triggered when a client sends data through the socket
     * @param SocketInterface The socket/connection that sent the message to your application
     * @param string The message received
     * @return Ratchet\Resource\Command\CommandInterface|null
     */
    function onMessage(SocketInterface $from, $msg);

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param SocketInterface The socket/connection that is closing/closed
     * @return Ratchet\Resource\Command\CommandInterface|null
     */
    function onClose(SocketInterface $conn);

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param SocketInterface
     * @param \Exception
     * @return Ratchet\Resource\Command\CommandInterface|null
     */
    function onError(SocketInterface $conn, \Exception $e);
}