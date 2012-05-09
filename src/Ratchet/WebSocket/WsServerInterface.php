<?php
namespace Ratchet\WebSocket;
use Ratchet\MessageComponentInterface;

interface WsServerInterface extends MessageComponentInterface {
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