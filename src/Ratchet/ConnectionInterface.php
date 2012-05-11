<?php
namespace Ratchet;

const VERSION = 'Ratchet/0.1';

/**
 * A proxy object representing a connection to the application
 * This acts as a container to storm data (in memory) about the connection
 */
interface ConnectionInterface {
    /**
     * Send data to the connection
     * @param string
     */
    function send($data);

    /**
     * Close the connection
     */
    function close();
}