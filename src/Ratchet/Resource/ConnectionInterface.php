<?php
namespace Ratchet\Resource;

const VERSION = 'Ratchet/0.1';

interface ConnectionInterface {
    /**
     * Send data to the connection
     * @param string
     */
    function write($data);

    /**
     * End the connection
     */
    function end();
}