<?php
namespace Ratchet\Component\WebSocket\Version\Hixie76;
use Ratchet\Component\WebSocket\Version\MessageInterface;
use Ratchet\Component\WebSocket\Version\FrameInterface;

class Message implements MessageInterface {
    /**
     * @var Ratchet\Component\WebSocket\Version\FrameInterface
     */
    protected $_frame = null;

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
        if (!($this->_frame instanceof FrameInterface)) {
            return false;
        }

        return $this->_frame->isCoalesced();
    }

    /**
     * {@inheritdoc}
     */
    public function addFrame(FrameInterface $fragment) {
        if (null !== $this->_frame) {
            throw new \OverflowException('Hixie76 does not support multiple framing of messages');
        }

        $this->_frame = $fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function getOpcode() {
        // Hixie76 only supported text messages
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayloadLength() {
        throw new \DomainException('Please sir, may I have some code? (' . __FUNCTION__ . ')');
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload() {
        if (!$this->isCoalesced()) {
            throw new \UnderflowException('Message has not been fully buffered yet');
        }

        return $this->_frame->getPayload();
    }
}