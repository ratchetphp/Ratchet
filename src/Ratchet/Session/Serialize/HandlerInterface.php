<?php

namespace Ratchet\Session\Serialize;

interface HandlerInterface {
    /**
     * @param string
     * @return array
     */
    function unserialize($raw);
}
