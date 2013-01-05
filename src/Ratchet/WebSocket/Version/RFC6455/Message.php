<?php
namespace Ratchet\WebSocket\Version\RFC6455;
use Ratchet\WebSocket\Version\MessageInterface;
use Ratchet\WebSocket\Version\FrameInterface;

class Message implements MessageInterface, \Countable {
    /**
     * @var \SplDoublyLinkedList
     */
    protected $_frames;

    public function __construct() {
        $this->_frames = new \SplDoublyLinkedList;
    }

    /**
     * {@inheritdoc}
     */
    public function count() {
        return count($this->_frames);
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
     * @todo Also, I should perhaps check the type...control frames (ping/pong/close) are not to be considered part of a message
     */
    public function addFrame(FrameInterface $fragment) {
        $this->_frames->push($fragment);

        return $this;
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
                // Not an error, want the current amount buffered
            }
        }

        return $len;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload() {
        if (!$this->isCoalesced()) {
            throw new \UnderflowException('Message has not been put back together yet');
        }

        $buffer = '';

        foreach ($this->_frames as $frame) {
            $buffer .= $frame->getPayload();
        }

        return $buffer;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents() {
        if (!$this->isCoalesced()) {
            throw new \UnderflowException("Message has not been put back together yet");
        }

        $buffer = '';

        foreach ($this->_frames as $frame) {
            $buffer .= $frame->getContents();
        }

        return $buffer;
    }
}