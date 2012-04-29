<?php
namespace Ratchet\Component\WebSocket\Version\RFC6455;
use Ratchet\Component\WebSocket\Version\MessageInterface;
use Ratchet\Component\WebSocket\Version\FrameInterface;

class Message implements MessageInterface {
    /**
     * @var SplDoublyLinkedList
     */
    protected $_frames;

    public function __construct() {
        $this->_frames = new \SplDoublyLinkedList;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString() {
        return $this->getPayload();
    }

    /**
     * {@inheritdoc}
     */
    public function isCoalesced() {
        if (count($this->_frames) == 0) {
            return false;
        }

        $last = $this->_frames->top();

        return ($last->isCoalesced() && $last->isFinal());
    }

    /**
     * {@inheritdoc}
     * @todo Should I allow addFrame if the frame is not coalesced yet?  I believe I'm assuming this class will only receive fully formed frame messages
     * @todo Also, I should perhaps check the type...control frames (ping/pong/close) are not to be considered part of a message
     */
    public function addFrame(FrameInterface $fragment) {
        $this->_frames->push($fragment);
    }

    /**
     * {@inheritdoc}
     */
    public function getOpcode() {
        if (count($this->_frames) == 0) {
            throw new \UnderflowException('No frames have been added to this message');
        }

        return $this->_frames->bottom()->getOpcode();
    }

    /**
     * {@inheritdoc}
     */
    public function getPayloadLength() {
        $len = 0;

        foreach ($this->_frames as $frame) {
            try {
                $len += $frame->getPayloadLength();
            } catch (\UnderflowException $e) {
            }
        }

        return $len;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload() {
        if (!$this->isCoalesced()) {
            throw new \UnderflowMessage('Message has not been put back together yet');
        }

        $buffer = '';

        foreach ($this->_frames as $frame) {
            $buffer .= $frame->getPayload();
        }

        return $buffer;
    }
}