<?php
namespace Ratchet\Application\WebSocket;
use Ratchet\ObserverInterface;
use Ratchet\SocketInterface;

/**
 * @todo App interfaces this (optionally) if is meant for WebSocket
 * @todo WebSocket checks if instanceof AppInterface, if so uses getSubProtocol() when doing handshake
 */
interface AppInterface extends ObserverInterface {
    /**
     * @return string
     */
    function getSubProtocol();

    /**
     * @param Ratchet\SocketInterface
     * @param string
     * @return Ratchet\Resource\Command\CommandInterface|null
     */
    function onOpen(SocketInterface $conn, $headers);
}