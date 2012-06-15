<?php
namespace Ratchet\WebSocket\Version\Hixie76;
use Ratchet\AbstractConnectionDecorator;

/**
 * {@inheritdoc}
 */
class Connection extends AbstractConnectionDecorator {
    public function send($msg) {
        return $this->getConnection()->send(chr(0) . $msg . chr(255));
    }

    public function close() {
        return $this->getConnection()->close();
    }
}