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
     * Currently instead of this, I'm setting header in the Connection object passed around...not sure which I like more
     * @param string
     */
    //function setHeaders($headers);

    /**
     * @return string
     */
    function getSubProtocol();
}