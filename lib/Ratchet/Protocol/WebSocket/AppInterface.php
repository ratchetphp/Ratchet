<?php
namespace Ratchet\Protocol\WebSocket;
use Ratchet\ReceiverInterface;

/**
 * @todo App interfaces this (optionally) if is meant for WebSocket
 * @todo WebSocket checks if instanceof AppInterface, if so uses getSubProtocol() when doing handshake
 */
interface AppInterface extends ReceiverInterface {
    /**
     * @return string
     */
    function getSubProtocol();
}