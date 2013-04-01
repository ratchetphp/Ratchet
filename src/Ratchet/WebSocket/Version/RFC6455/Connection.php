<?php
namespace Ratchet\WebSocket\Version\RFC6455;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\WebSocket\Version\DataInterface;

/**
 * {@inheritdoc}
 * @property \StdClass $WebSocket
 */
class Connection extends AbstractConnectionDecorator {
    public function send($msg) {
        if (!($msg instanceof DataInterface)) {
            $msg = new Frame($msg);
        }

        $this->getConnection()->send($msg->getContents());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function close($code = 1000) {
        if ($code instanceof DataInterface) {
            $this->send($code);
        } else {
            $this->send(new Frame(pack('n', $code), true, Frame::OP_CLOSE));
        }

        $this->getConnection()->close();
    }
}