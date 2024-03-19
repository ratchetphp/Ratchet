<?php

namespace Ratchet\Session;

interface OptionsHandlerInterface
{
    public function get(string $name) : string;

    /**
     * @param mixed $value
     */
    public function set(string $name, $value) : void;
}
