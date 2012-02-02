<?php
namespace Ratchet\Component\WebSocket;
use Ratchet\Component\MessageComponentInterface;

interface WebSocketComponentInterface extends MessageComponentInterface {
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