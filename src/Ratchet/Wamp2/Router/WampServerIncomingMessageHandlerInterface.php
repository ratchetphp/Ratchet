<?php

namespace Ratchet\Wamp2\Router;


use Ratchet\Wamp2\Router\WampClientProxyInterface;

interface WampServerIncomingMessageHandlerInterface {

    /**
     * Triggered when a client sends a WAMP message through the socket
     * @param  \Ratchet\WampClientInterface $client The WAMP client that sent the message to your application
     * @param  array                       $message  The message received
     * @throws \Exception
     */
    function handleMessage(WampClientProxyInterface $client, array $message);
}