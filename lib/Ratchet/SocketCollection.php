<?php
namespace Ratchet;

class SocketCollection extends \SplQueue {
    public function __construct() {
//        parent::__construct();
        $this->setIteratorMode(static::IT_MODE_DELETE);
    }

    public function enqueue(SocketInterface $value) {
        parent::enqueue($value);
    }
}