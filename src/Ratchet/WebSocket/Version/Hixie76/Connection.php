<?php
namespace Ratchet\WebSocket\Version\Hixie76;
use Ratchet\AbstractConnectionDecorator;

/**
 * {@inheritdoc}
 * @property \StdClass $WebSocket
 */
class Connection extends AbstractConnectionDecorator {
    public function send($msg) {
        if (!$this->WebSocket->closing) {
            $this->getConnection()->send(chr(0) . $msg . chr(255));
        }

        return $this;
    }

    public function close() {
        if (!$this->WebSocket->closing) {
            $this->getConnection()->send(chr(255));
            $this->getConnection()->close();

            $this->WebSocket->closing = true;
        }
    }
}
