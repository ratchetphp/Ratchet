<?php
namespace Ratchet\Resource;

interface ConnectionInterface {
    /**
     * @return int
     */
    function getId();


    /**
     * Set an attribute to the connection
     * @param mixed
     * @param mixed
     */
    function __set($name, $value);

    /**
     * Get a previously set attribute bound to the connection
     * @return mixed
     * @throws \InvalidArgumentException
     */
    function __get($name);

    /**
     * @param mixed
     * @return bool
     */
    function __isset($name);

    /**
     * @param mixed
     */
    function __unset($name);
}