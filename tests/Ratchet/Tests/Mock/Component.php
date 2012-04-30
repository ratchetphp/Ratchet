<?php
namespace Ratchet\Tests\Mock;
use Ratchet\Component\MessageComponentInterface;
use Ratchet\Tests\Mock\Socket as MockSocket;
use Ratchet\Resource\ConnectionInterface;

/**
 * @todo Rename to MessageComponent
 */
class Component implements MessageComponentInterface {
    public $last = array();

    public function __construct(ComponentInterface $app = null) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onClose(ConnectionInterface $conn) {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->last[__FUNCTION__] = func_get_args();
    }
}