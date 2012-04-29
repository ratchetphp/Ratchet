<?php
namespace Ratchet\Component\WebSocket\Version\RFC6455;
use Ratchet\Component\WebSocket\Version\FrameInterface;

class Frame implements FrameInterface {
    /**
     * The contents of the frame
     * @var string
     */
    protected $_data = '';

    /**
     * Number of bytes received from the frame
     * @var int
     */
    public $_bytes_rec = 0;

    /**
     * Number of bytes in the payload (as per framing protocol)
     * @var int
     */
    protected $_pay_len_def = -1;

    /**
     * Bit 9-15
     * @var int
     */
    protected $_pay_check = -1;

    /**
     * {@inheritdoc}
     */
    public function isCoalesced() {
        try {
            $payload_length = $this->getPayloadLength();
            $payload_start  = $this->getPayloadStartingByte();
        } catch (\UnderflowException $e) {
            return false;
        }

        return $payload_length + $payload_start === $this->_bytes_rec;        
    }

    /**
     * {@inheritdoc}
     */
    public function addBuffer($buf) {
        $buf = (string)$buf;

        $this->_data      .= $buf;
        $this->_bytes_rec += strlen($buf);
    }

    /**
     * {@inheritdoc}
     */
    public function isFinal() {
        if ($this->_bytes_rec < 1) {
            throw new \UnderflowException('Not enough bytes received to determine if this is the final frame in message');
        }

        $fbb = sprintf('%08b', ord($this->_data[0]));
        return (boolean)(int)$fbb[0];
    }

    /**
     * {@inheritdoc}
     */
    public function isMasked() {
        if ($this->_bytes_rec < 2) {
            throw new \UnderflowException("Not enough bytes received ({$this->_bytes_rec}) to determine if mask is set");
        }

        return (boolean)bindec(substr(sprintf('%08b', ord($this->_data[1])), 0, 1));
    }

    /**
     * {@inheritdoc}
     */
    public function getOpcode() {
        if ($this->_bytes_rec < 1) {
            throw new \UnderflowException('Not enough bytes received to determine opcode');
        }

        return bindec(substr(sprintf('%08b', ord($this->_data[0])), 4, 4));
    }

    /**
     * Gets the decimal value of bits 9 (10th) through 15 inclusive
     * @return int
     * @throws UnderflowException If the buffer doesn't have enough data to determine this
     */
    protected function getFirstPayloadVal() {
        if ($this->_bytes_rec < 2) {
            throw new \UnderflowException('Not enough bytes received');
        }

        return ord($this->_data[1]) & 127;
    }

    /**
     * @return int (7|23|71) Number of bits defined for the payload length in the fame
     * @throws UnderflowException
     */
    protected function getNumPayloadBits() {
        if ($this->_bytes_rec < 2) {
            throw new \UnderflowException('Not enough bytes received');
        }

        // By default 7 bits are used to describe the payload length
        // These are bits 9 (10th) through 15 inclusive
        $bits = 7;

        // Get the value of those bits
        $check = $this->getFirstPayloadVal();

        // If the value is 126 the 7 bits plus the next 16 are used to describe the payload length
        if ($check >= 126) {
            $bits += 16;
        }

        // If the value of the initial payload length are is 127 an additional 48 bits are used to describe length 
        // Note: The documentation specifies the length is to be 63 bits, but I think that's a type and is 64 (16+48)
        if ($check === 127) {
            $bits += 48;
        }

        if (!in_array($bits, array(7, 23, 71))) {
            throw new \UnexpectedValueException("Malformed frame, invalid payload length provided");
        }

        return $bits;
    }

    /**
     * This just returns the number of bytes used in the frame to describe the payload length (as opposed to # of bits)
     * @see getNumPayloadBits
     */
    protected function getNumPayloadBytes() {
        return (1 + $this->getNumPayloadBits()) / 8;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayloadLength() {
        if ($this->_pay_len_def !== -1) {
            return $this->_pay_len_def;
        }

        $length_check = $this->getFirstPayloadVal();

        if ($length_check <= 125) {
            $this->_pay_len_def = $length_check;
            return $this->getPayloadLength();
        }

        $byte_length = $this->getNumPayloadBytes();
        if ($this->_bytes_rec < 1 + $byte_length) {
            throw new \UnderflowException('Not enough data buffered to determine payload length');
        }

        $strings = array();
        for ($i = 2; $i < $byte_length + 1; $i++) {
            $strings[] = ord($this->_data[$i]);
        }

        $this->_pay_len_def = bindec(vsprintf(str_repeat('%08b', $byte_length - 1), $strings));
        return $this->getPayloadLength();
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskingKey() {
        if (!$this->isMasked()) {
            return '';
        }

        $length = 4;
        $start  = 1 + $this->getNumPayloadBytes();

        if ($this->_bytes_rec < $start + $length) {
            throw new \UnderflowException('Not enough data buffered to calculate the masking key');
        }

        return substr($this->_data, $start, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function getPayloadStartingByte() {
        return 1 + $this->getNumPayloadBytes() + strlen($this->getMaskingKey());
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload() {
        if (!$this->isCoalesced()) {
            throw new \UnderflowException('Can not return partial message');
        }

        $payload = '';
        $length  = $this->getPayloadLength();

        if ($this->isMasked()) {
            $mask  = $this->getMaskingKey();
            $start = $this->getPayloadStartingByte();

            for ($i = 0; $i < $length; $i++) {
                $payload .= $this->_data[$i + $start] ^ $mask[$i % 4];
            }
        } else {
            $payload = substr($this->_data, $start, $this->getPayloadLength());
        }

        if (strlen($payload) !== $length) {
            // Is this possible?  isCoalesced() math _should_ ensure if there is mal-formed data, it would return false
            throw new \UnexpectedValueException('Payload length does not match expected length');
        }

        return $payload;
    }
}