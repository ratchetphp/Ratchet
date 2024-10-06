<?php

namespace Ratchet\WebSocket;
use Override;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\DataInterface;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\RFC6455\Messaging\FrameInterface;

/**
 * @property \StdClass $WebSocket
 */
class WsConnection extends AbstractConnectionDecorator
{
    #[Override]
    public function send(string $data): ConnectionInterface
    {
        if (! $this->WebSocket->closing) {
            if (! ($data instanceof DataInterface)) {
                $data = new Frame($data);
            }

            $this->getConnection()->send($data->getContents());
        }

        return $this;
    }

    /**
     * @param int|FrameInterface $code
     *
     */
    #[Override]
    public function close(int|FrameInterface $code = 1000): void
    {
        if ($this->WebSocket->closing) {
            return;
        }

        if ($code instanceof DataInterface) {
            $this->send($code);
        } else {
            $this->send(new Frame(pack('n', $code), true, Frame::OP_CLOSE));
        }

        $this->getConnection()->close();

        $this->WebSocket->closing = true;
    }
}
