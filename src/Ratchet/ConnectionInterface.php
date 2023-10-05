<?php
namespace Ratchet;

/**
 * The version of Ratchet being used
 * @var string
 */
const VERSION = 'Ratchet/0.4.4';

/**
 * A proxy object representing a connection to the application
 * This acts as a container to store data (in memory) about the connection
 *
 * Note that Ratchet makes heavy use of the decorator pattern and dynamic
 * properties. This means any object that implements this interface may not
 * have the same methods or properties as the ones in this interface.
 * Implementations of this interface may have to use the `#[\AllowDynamicProperties]`
 * attribute to allow dynamic properties on PHP 8.2+.
 */
interface ConnectionInterface {
    /**
     * Send data to the connection
     * @param  string $data
     * @return \Ratchet\ConnectionInterface
     */
    function send($data);

    /**
     * Close the connection
     */
    function close();
}
