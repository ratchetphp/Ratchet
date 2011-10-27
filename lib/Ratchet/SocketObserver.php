<?php
namespace Ratchet;

interface SocketObserver {
    /**
     * @param SocketInterface
     */
    function onOpen(SocketInterface $conn);

    /**
     * @param SocketInterface
     * @param string
     */
    function onRecv(SocketInterface $from, $msg);

    /**
     * @param SocketInterface
     */
    function onClose(SocketInterface $conn);
}