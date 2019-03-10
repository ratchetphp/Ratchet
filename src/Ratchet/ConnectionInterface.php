<?php
namespace Ratchet;

use Psr\Container\ContainerInterface;
/**
 * The version of Ratchet being used
 * @var string
 */
const VERSION = 'Ratchet/0.4.1';

/**
 * A proxy object representing a connection to the application
 * This acts as a container to store data (in memory) about the connection
 */
interface ConnectionInterface extends ContainerInterface {
    // function id(): string  --- maybe make lookups easier?

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
