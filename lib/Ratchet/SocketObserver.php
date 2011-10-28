<?php
namespace Ratchet;

/**
 * Observable/Observer design pattern interface for handing events on a socket
 */
interface SocketObserver {
    /**
     * When a new connection is opened it will be passed to this method
     * @param SocketInterface
     */
    function onOpen(SocketInterface $conn);

    /**
     * Triggered when a client sends data through the socket
     * @param SocketInterface
     * @param string
     */
    function onRecv(SocketInterface $from, $msg);

    /**
     * This is called just before the connection is closed
     * @param SocketInterface
     */
    function onClose(SocketInterface $conn);
}