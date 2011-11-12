<?php
namespace Ratchet\Protocol\WebSocket;
use Ratchet\SocketObserver;
use Ratchet\SocketInterface;

/**
 * @todo App interfaces this (optionally) if is meant for WebSocket
 * @todo WebSocket checks if instanceof AppInterface, if so uses getSubProtocol() when doing handshake
 */
interface AppInterface extends SocketObserver {
    /**
     * @return string
     */
    function getSubProtocol();

    /**
     * @param Ratchet\SocketInterface
     * @param string
     * @return Ratchet\Command\CommandInterface|null
     */
    function onOpen(SocketInterface $conn, $headers);
}