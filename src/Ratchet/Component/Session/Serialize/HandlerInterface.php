<?php
namespace Ratchet\Component\Session\Serialize;

interface HandlerInterface {
    /**
     * @param array
     * @return string
     */
    function serialize(array $data);

    /**
     * @param string
     * @return array
     */
    function unserialize($raw);
}