<?php

namespace Ratchet\Session\Serialize;

interface HandlerInterface
{
    /**
     * @param array
     *
     * @return string
     */
    public function serialize(array $data);

    /**
     * @param string
     *
     * @return array
     */
    public function unserialize($raw);
}
