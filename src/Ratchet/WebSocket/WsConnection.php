<?php
namespace Ratchet\WebSocket;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Handshake\PermessageDeflateOptions;
use Ratchet\RFC6455\Messaging\CloseFrameChecker;
use Ratchet\RFC6455\Messaging\DataInterface;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\RFC6455\Messaging\FrameInterface;
use Ratchet\RFC6455\Messaging\MessageBuffer;
use Ratchet\RFC6455\Messaging\MessageInterface;

/**
 * {@inheritdoc}
 * @property \StdClass $WebSocket
 */
class WsConnection extends AbstractConnectionDecorator {
    /** @var MessageBuffer */
    private $streamer;

    public function __construct(ConnectionInterface $conn, callable $onMessage, callable $onControlFrame, PermessageDeflateOptions $pmdOptions = null) {
        parent::__construct($conn);

        $closeFrameChecker = new CloseFrameChecker();

        $reusableUnderflowException = new \UnderflowException;

        $this->streamer = new MessageBuffer(
            $closeFrameChecker,
            function(MessageInterface $msg) use ($onMessage) {
                $onMessage($this, $msg);
            },
            function(FrameInterface $frame) use ($onControlFrame) {
                $onControlFrame($frame, $this);
            },
            true,
            function() use ($reusableUnderflowException) {
                return $reusableUnderflowException;
            },
            null,
            null,
            [$conn, 'send'],
            $pmdOptions
        );
    }


    /**
     * {@inheritdoc}
     */
    public function send($msg) {
        if (!$this->WebSocket->closing) {
            if (!($msg instanceof DataInterface)) {
                $msg = new Frame($msg);
            }

            $this->streamer->sendFrame($msg);
        }

        return $this;
    }

    /**
     * @param int|\Ratchet\RFC6455\Messaging\DataInterface
     */
    public function close($code = 1000) {
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

    /**
     * @return MessageBuffer
     */
    public function getStreamer()
    {
        return $this->streamer;
    }
}
