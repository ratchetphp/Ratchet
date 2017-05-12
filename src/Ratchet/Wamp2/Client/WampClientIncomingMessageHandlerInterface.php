<?php

namespace Ratchet\Wamp2\Client;

interface WampClientIncomingMessageHandlerInterface {
    function handleMessage(array $message);
}