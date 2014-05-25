<?php

namespace Ratchet\Wamp2;


interface WampFormatterInterface {
    function serialize(array $message);

    function deserialize($raw);
} 