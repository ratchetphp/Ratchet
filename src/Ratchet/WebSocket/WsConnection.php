<?php
namespace Ratchet\WebSocket;
use Ratchet\ConnectionInterface;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\WebSocket\Version\VersionInterface;
use Ratchet\WebSocket\Version\FrameInterface;

use Ratchet\WebSocket\Version\RFC6455\Frame;

/**
 * {@inheritdoc}
 * @property stdClass $WebSocket
 */
class WsConnection extends AbstractConnectionDecorator {
    public function __construct(ConnectionInterface $conn) {
        parent::__construct($conn);

        $this->WebSocket = new \StdClass;
    }

    public function send($data) {
        if ($data instanceof FrameInterface) {
            $data = $data->data;
        } elseif (isset($this->WebSocket->version)) {
            // need frame caching
            $data = $this->WebSocket->version->frame($data, false);
        }

        $this->getConnection()->send($data);
    }

    /**
     * {@inheritdoc}
     * @todo If code is 1000 send close frame - false is close w/o frame...?
     */
    public function close($code = 1000) {
        $this->send(Frame::create($code, true, Frame::OP_CLOSE));
        // send close frame with code 1000

        // ???

        // profit

        $this->getConnection()->close(); // temporary
    }
}