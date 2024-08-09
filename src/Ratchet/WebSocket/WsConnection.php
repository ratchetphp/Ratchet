<?php
namespace Ratchet\WebSocket;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\RFC6455\Messaging\DataInterface;
use Ratchet\RFC6455\Messaging\Frame;

/**
 * {@inheritdoc}
 * @property \StdClass $WebSocket
 */
class WsConnection extends AbstractConnectionDecorator {
    /**
     * {@inheritdoc}
     */
    public function send($msg) {
        if (!$this->WebSocket->closing) {
            if (!($msg instanceof DataInterface)) {
                $msg = new Frame($msg);
            }

            $this->getConnection()->send($msg->getContents());
        }

        return $this;
    }

    /**
     * @param int|\Ratchet\RFC6455\Messaging\DataInterface $code
     * @param null|string $reason
     */
    public function close($code = 1000, $reason = null) {
        if ($this->WebSocket->closing) {
            return;
        }

        if ($code instanceof DataInterface) {
            $this->send($code);
        } else {
            if (!is_string($reason)) {
                $frame = new Frame(pack('n', $code), true, Frame::OP_CLOSE);
            } else {
                // Limit reason to 123 bytes to fit into the remainder of the 125 byte payload limit
                while (strlen($reason) > 123) {
                    $reason = substr($reason, 0, -1);
                }
                $frame = new Frame(pack('nA*', $code, $reason), true, Frame::OP_CLOSE);
            }

            $this->send($frame);
        }

        $this->getConnection()->close();

        $this->WebSocket->closing = true;
    }
}
