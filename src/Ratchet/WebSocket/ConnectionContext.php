<?php
namespace Ratchet\WebSocket;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\RFC6455\Messaging\Protocol\Frame;
use Ratchet\RFC6455\Messaging\Protocol\FrameInterface;
use Ratchet\RFC6455\Messaging\Protocol\MessageInterface;
use Ratchet\RFC6455\Messaging\Streaming\ContextInterface;

class ConnectionContext implements ContextInterface {
    private $message;
    private $frame;

    private $conn;
    private $component;

    public function __construct(ConnectionInterface $conn, MessageComponentInterface $component) {
        $this->conn = $conn;
        $this->component = $component;
    }

    public function detach() {
        $conn = $this->conn;

        $this->frame   = null;
        $this->message = null;

        $this->component = null;
        $this->conn      = null;

        return $conn;
    }

    public function onError(\Exception $e) {
        $this->component->onError($this->conn, $e);
    }

    public function setFrame(FrameInterface $frame = null) {
        $this->frame = $frame;

        return $frame;
    }

    /**
     * @return \Ratchet\RFC6455\Messaging\Protocol\FrameInterface
     */
    public function getFrame() {
        return $this->frame;
    }

    public function setMessage(MessageInterface $message = null) {
        $this->message = $message;

        return $message;
    }

    /**
     * @return \Ratchet\RFC6455\Messaging\Protocol\MessageInterface
     */
    public function getMessage() {
        return $this->message;
    }

    public function onMessage(MessageInterface $msg) {
        $this->component->onMessage($this->conn, $msg->getPayload());
    }

    public function onPing(FrameInterface $frame) {
        $pong = new Frame($frame->getPayload(), true, Frame::OP_PONG);

        $this->conn->send($pong);
    }

    public function onPong(FrameInterface $frame) {
        // TODO: Implement onPong() method.
    }

    /**
     * @param $code int
     */
    public function onClose($code) {
        $this->conn->close($code);
    }
}