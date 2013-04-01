<?php
namespace Ratchet\WebSocket\Version\Hixie76;
use Ratchet\AbstractConnectionDecorator;

/**
 * {@inheritdoc}
 * @property \StdClass $WebSocket
 */
class Connection extends AbstractConnectionDecorator {
    public function send($msg) {
        $this->getConnection()->send(chr(0) . $msg . chr(255));

        return $this;
    }

    public function close() {
        $this->getConnection()->close();
    }
}