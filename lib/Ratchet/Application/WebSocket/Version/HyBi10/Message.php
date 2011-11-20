<?php
namespace Ratchet\Application\WebSocket\Version\HyBi10;
use Ratchet\Application\WebSocket\Version\MessageInterface;
use Ratchet\Application\WebSocket\Version\FrameInterface;

class Message implements MessageInterface {
    /**
     * @var SplDoublyLinkedList
     */
    protected $_frames;

    public function __construct() {
        $this->_frames = new \SplDoublyLinkedList;
    }

    public function __toString() {
        return $this->getPayload();
    }

    public function isCoalesced() {
        if (count($this->_frames) == 0) {
            return false;
        }

        $last = $this->_frames->top();

        return ($last->isCoalesced() && $last->isFinal());
    }

    /**
     * @todo Should I allow addFrame if the frame is not coalesced yet?  I believe I'm assuming this class will only receive fully formed frame messages
     * @todo Also, I should perhaps check the type...control frames (ping/pong/close) are not to be considered part of a message
     */
    public function addFrame(FrameInterface $fragment) {
        $this->_frames->push($fragment);
    }

    public function getOpcode() {
        if (count($this->_frames) == 0) {
            throw new \UnderflowException('No frames have been added to this message');
        }

        return $this->_frames->bottom()->getOpcode();
    }

    public function getPayloadLength() {
        throw new \DomainException('Please sir, may I have some code? (' . __FUNCTION__ . ')');
    }

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