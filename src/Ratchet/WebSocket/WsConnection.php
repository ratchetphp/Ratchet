<?php
namespace Ratchet\WebSocket;
use Ratchet\AbstractConnectionDecorator;

/**
 * @property stdClass $WebSocket
 */
class WsConnection extends AbstractConnectionDecorator {
    public function send($data) {
        // need frame caching

        $data = $this->WebSocket->version->frame($data, false);

        $this->getConnection()->send($data);
    }

    public function close() {
        // send close frame

        // ???

        // profit

        $this->getConnection()->close(); // temporary
    }

    public function ping() {
    }

    public function pong() {
    }
}