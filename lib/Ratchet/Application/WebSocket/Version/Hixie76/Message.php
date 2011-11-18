<?php
namespace Ratchet\Application\WebSocket\Version\Hixie76;
use Ratchet\Application\WebSocket\Version\MessageInterface;
use Ratchet\Application\WebSocket\Version\FrameInterface;

class Message implements MessageInterface {
    /**
     * @var Ratchet\Application\WebSocket\Version\FrameInterface
     */
    protected $_frame = null;

    public function __toString() {
        return $this->getPayload();
    }

    public function isCoalesced() {
        if (!($this->_frame instanceof FrameInterface)) {
            return false;
        }

        return $this->_frame->isCoalesced();
    }

    public function addFrame(FrameInterface $fragment) {
        if (null !== $this->_frame) {
            throw new \OverflowException('Hixie76 does not support multiple framing of messages');
        }

        $this->_frame = $fragment;
    }

    public function getOpcode() {
        // Hixie76 only supported text messages
        return 1;
    }

    public function getPayloadLength() {
        throw new \DomainException('Please sir, may I have some code? (' . __FUNCTION__ . ')');
    }

    public function getPayload() {
        if (!$this->isCoalesced()) {
            throw new \UnderflowException('Message has not been fully buffered yet');
        }

        return $this->_frame->getPayload();
    }
}