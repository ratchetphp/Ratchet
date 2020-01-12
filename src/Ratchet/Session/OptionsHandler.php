<?php

namespace Ratchet\Session;

interface OptionsHandler
{
    public function get(string $name) : string;

    /**
     * @param mixed $value
     */
    public function set(string $name, $value) : void;
}
