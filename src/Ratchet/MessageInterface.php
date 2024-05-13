<?php
namespace Ratchet;

use Throwable;

interface MessageInterface {
    /**
     * Triggered when a client sends data through the socket
     * @param  \Ratchet\ConnectionInterface $from The socket/connection that sent the message to your application
     * @param  string                       $msg  The message received
     * @throws Throwable
     */
    function onMessage(ConnectionInterface $from, $msg);
}
