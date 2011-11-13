<?php
namespace Ratchet\Application\WebSocket;
use Ratchet\Application\ApplicationInterface;

/**
 * @todo App interfaces this (optionally) if is meant for WebSocket
 * @todo WebSocket checks if instanceof AppInterface, if so uses getSubProtocol() when doing handshake
 * @todo Pick a better name for this...
 */
interface WebSocketAppInterface extends ApplicationInterface {
    /**
     * @param string
     */
    function setHeaders($headers);

    /**
     * @return string
     */
    function getSubProtocol();
}