<?php
namespace Ratchet\Application\WebSocket\Version\Hixie76;
use Ratchet\Application\WebSocket\Version\FrameInterface;

/**
 * This does not entirely follow the protocol to spec, but (mostly) works
 * Hixie76 probably should not even be supported
 */
class Frame implements FrameInterface {
    /**
     * @type string
     */
    protected $_data = '';

    public function isCoalesced() {
        return (boolean)($this->_data[0] == chr(0) && substr($this->_data, -1) == chr(255));
    }

    public function addBuffer($buf) {
        $this->_data .= (string)$buf;
    }

    public function isFinal() {
        return true;
    }

    public function isMasked() {
        return false;
    }

    public function getOpcode() {
        return 1;
    }

    public function getPayloadLength() {
        if (!$this->isCoalesced()) {
            throw new \UnderflowException('Not enough of the message has been buffered to determine the length of the payload');
        }

        return strlen($this->_data) - 2;
    }

    public function getMaskingKey() {
        return '';
    }

    public function getPayload() {
        if (!$this->isCoalesced()) {
            return new \UnderflowException('Not enough data buffered to read payload');
        }

        return substr($this->_data, 1, strlen($this->_data) - 2);
    }
}