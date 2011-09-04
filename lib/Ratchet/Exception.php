<?php
namespace Ratchet;

class Exception extends \Exception {
    public function __construct() {
        $int = socket_last_error();
        parent::__construct(socket_strerror($int), $int);
    }
}