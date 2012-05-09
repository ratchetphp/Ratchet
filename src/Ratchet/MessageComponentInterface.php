<?php
namespace Ratchet;
use Ratchet\ConnectionInterface;

interface MessageComponentInterface extends ComponentInterface {
    /**
     * Triggered when a client sends data through the socket
     * @param Ratchet\ConnectionInterface The socket/connection that sent the message to your application
     * @param string The message received
     * @throws Exception
     */
    function onMessage(ConnectionInterface $from, $msg);
}