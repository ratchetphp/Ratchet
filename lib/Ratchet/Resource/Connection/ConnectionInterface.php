<?php
namespace Ratchet\Resource\Connection;
use Ratchet\SocketInterface;

interface ConnectionInterface {
    /**
     * The socket this representative connection is tied to
     * @param Ratchet\SocketInterface
     */
    function __construct(SocketInterface $socket);

    /**
     * @return scalar
     */
    function getID();

    /**
     * Set an attribute to the connection
     * @param string
     * @param mixed
     */
    function set($name, $val);

    /**
     * Get a previously set attribute bound to the connection
     * @return mixed
     * @throws \UnexpectedValueException
     */
    function get($name);
}