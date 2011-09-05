<?php
namespace Ratchet;

class Exception extends \Exception {
    public function __construct() {
        $int = socket_last_error();
        $msg = socket_strerror($int);

        // todo, replace {$msg: $int} to {$msg}

        parent::__construct($msg, $int);
    }
}