<?php
namespace Ratchet\WebSocket\Version\Hixie76;
use Ratchet\WebSocket\Version\FrameInterface;

/**
 * This does not entirely follow the protocol to spec, but (mostly) works
 * Hixie76 probably should not even be supported
 */
class Frame implements FrameInterface {
    /**
     * @type string
     */
    protected $_data = '';

    /**
     * {@inheritdoc}
     */
    public function isCoalesced() {
        return (boolean)($this->_data[0] == chr(0) && substr($this->_data, -1) == chr(255));
    }

    /**
     * {@inheritdoc}
     */
    public function addBuffer($buf) {
        $this->_data .= (string)$buf;
    }

    /**
     * {@inheritdoc}
     */
    public function isFinal() {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isMasked() {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getOpcode() {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayloadLength() {
        if (!$this->isCoalesced()) {
            throw new \UnderflowException('Not enough of the message has been buffered to determine the length of the payload');
        }

        return strlen($this->_data) - 2;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskingKey() {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload() {
        if (!$this->isCoalesced()) {
            return new \UnderflowException('Not enough data buffered to read payload');
        }

        return substr($this->_data, 1, strlen($this->_data) - 2);
    }

    public function getContents() {
        return $this->_data;
    }

    public function extractOverflow() {
        return '';
    }
}