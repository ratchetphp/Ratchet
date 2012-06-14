<?php
namespace Ratchet\WebSocket\Version\RFC6455;
use Ratchet\ConnectionInterface;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\WebSocket\Version\FrameInterface;

/**
 * {@inheritdoc}
 */
class Connection extends AbstractConnectionDecorator {
    public function send($msg) {
        if (!($msg instanceof FrameInterface)) {
            $msg = new Frame($msg);
        }

        $this->getConnection()->send($msg->getContents());
    }

    /**
     * {@inheritdoc}
     */
    public function close($code = 1000) {
        $this->send(new Frame($code, true, Frame::OP_CLOSE));

        $this->getConnection()->close();
    }
}