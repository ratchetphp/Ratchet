<?php
namespace Ratchet\Tests\Mock;
use Ratchet\Logging\LoggerInterface;

class ArrayLogger implements LoggerInterface {
    public $last_msg = '';

    public function note($msg) {
        $this->last_msg = $msg;
    }

    public function warning($msg) {
        $this->last_msg = $msg;
    }

    public function error($msg) {
        $this->last_msg = $msg;
    }
}