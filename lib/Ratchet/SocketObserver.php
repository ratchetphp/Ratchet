<?php
namespace Ratchet;

/**
 * Observable/Observer design pattern interface for handing events on a socket
 * @todo Consider an onException method.  Since server is running its own loop the app currently doesn't know when a problem is handled
 * @todo Consider an onDisconnect method for a server-side close()'ing of a connection - onClose would be client side close()
 * @todo Consider adding __construct(SocketObserver $decorator = null) - on Server move Socket as parameter to run()
 */
interface SocketObserver {
    /**
     * When a new connection is opened it will be passed to this method
     * @param SocketInterface The socket/connection that just connected to your application
     * @return Ratchet\Command\CommandInterface|null
     */
    function onOpen(SocketInterface $conn);

    /**
     * Triggered when a client sends data through the socket
     * @param SocketInterface The socket/connection that sent the message to your application
     * @param string The message received
     * @return Ratchet\Command\CommandInterface|null
     */
    function onRecv(SocketInterface $from, $msg);

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param SocketInterface The socket/connection that is closing/closed
     * @return Ratchet\Command\CommandInterface|null
     */
    function onClose(SocketInterface $conn);

    function onError(SocketInterface $conn, \Exception $e);
}