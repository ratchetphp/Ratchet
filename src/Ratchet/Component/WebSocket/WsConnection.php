<?php
namespace Ratchet\Component\WebSocket;
use Ratchet\Resource\AbstractConnectionDecorator;
use Ratchet\Resrouce\ConnectionInterface;

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