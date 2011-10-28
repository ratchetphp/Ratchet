<?php
namespace Ratchet;

/**
 * Uses internal php methods to fill an Exception class (no parameters required)
 */
class Exception extends \Exception {
    public function __construct() {
        $int = socket_last_error();
        $msg = socket_strerror($int);

        // todo, replace {$msg: $int} to {$msg}

        parent::__construct($msg, $int);
    }
}