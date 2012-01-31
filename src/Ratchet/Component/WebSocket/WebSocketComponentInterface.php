<?php
namespace Ratchet\Component\WebSocket;
use Ratchet\Component\ComponentInterface;

/**
 * @todo App interfaces this (optionally) if is meant for WebSocket
 * @todo WebSocket checks if instanceof AppInterface, if so uses getSubProtocol() when doing handshake
 * @todo Pick a better name for this...
 */
interface WebSocketComponentInterface extends ComponentInterface {
    /**
     * Currently instead of this, I'm setting header in the Connection object passed around...not sure which I like more
     * @param string
     */
    //function setHeaders($headers);

    /**
     * @return string
     */
    function getSubProtocol();
}