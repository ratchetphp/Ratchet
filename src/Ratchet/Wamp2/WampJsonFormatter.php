<?php

namespace Ratchet\Wamp2;


class WampJsonFormatter implements WampFormatterInterface {

    function serialize(array $message)
    {
        return json_encode($message);
    }

    function deserialize($raw)
    {
        return json_decode($raw, true);
    }
}