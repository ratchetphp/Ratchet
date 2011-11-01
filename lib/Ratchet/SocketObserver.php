<?php
namespace Ratchet;

/**
 * Observable/Observer design pattern interface for handing events on a socket
 */
interface SocketObserver {
    /**
     * When a new connection is opened it will be passed to this method
     * @param SocketInterface
     * @return Command\CommandInterface|NULL
     */
    function onOpen(SocketInterface $conn);

    /**
     * Triggered when a client sends data through the socket
     * @param SocketInterface
     * @param string
     * @return Command\CommandInterface|NULL
     */
    function onRecv(SocketInterface $from, $msg);

    /**
     * This is called just before the connection is closed
     * @param SocketInterface
     * @return Command\CommandInterface|NULL
     */
    function onClose(SocketInterface $conn);
}