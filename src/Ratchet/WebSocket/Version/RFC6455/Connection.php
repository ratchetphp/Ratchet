<?php
namespace Ratchet\WebSocket\Version\RFC6455;
use Ratchet\ConnectionInterface;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\WebSocket\Version\VersionInterface;
use Ratchet\WebSocket\Version\FrameInterface;

/**
 * {@inheritdoc}
 */
class Connection extends AbstractConnectionDecorator {
    public function __construct(ConnectionInterface $conn) {
        parent::__construct($conn);
    }

    public function send($msg) {
        if ($msg instanceof FrameInterface) {
            $data = $msg->data;
        } else {
            $frame = new Frame($msg);
            $data  = $frame->data;
        }

        $this->getConnection()->send($data);
    }

    /**
     * {@inheritdoc}
     */
    public function close($code = 1000) {
        $frame = new Frame($code, true, Frame::OP_CLOSE);

        $this->send($frame->data);

        $this->getConnection()->close();
    }
}