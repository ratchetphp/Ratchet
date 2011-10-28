<?php
namespace Ratchet;

/**
 * A self-deprecating queue that can only hold Socket objects
 */
class SocketCollection extends \SplQueue {
    public function __construct() {
        $this->setIteratorMode(static::IT_MODE_DELETE);
    }

    /**
     * @param SocketInterface
     */
    public function enqueue(SocketInterface $value) {
        parent::enqueue($value);
    }
}