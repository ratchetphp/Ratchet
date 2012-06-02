<?php
namespace Ratchet\WebSocket\Version\RFC6455;
use Ratchet\WebSocket\Version\FrameInterface;

class Frame implements FrameInterface {
    const OP_CONTINUE = 0;
    const OP_TEXT     = 1;
    const OP_BINARY   = 2;
    const OP_CLOSE    = 8;
    const OP_PING     = 9;
    const OP_PONG     = 10;

    /**
     * The contents of the frame
     * @var string
     */
    public $data = '';

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
     * @param string A valid UTF-8 string to send over the wire
     * @param bool Is the final frame in a message
     * @param int The opcode of the frame, see constants
     * @param bool Mask the payload
     * @return Frame
     * @throws InvalidArgumentException If the payload is not a valid UTF-8 string
     * @throws BadMethodCallException If there is a problem with miss-matching parameters
     * @throws LengthException If the payload is too big
     */
    public static function create($payload, $final = true, $opcode = 1, $mask = false) {
        $frame = new static();

        if (!mb_check_encoding($payload, 'UTF-8')) {
            throw new \InvalidArgumentException("Payload is not a valid UTF-8 string");
        }

        if (false === (boolean)$final && $opcode !== static::OP_CONTINUE) {
            throw new \BadMethodCallException("opcode MUST be 'continue' if the frame is not final");
        }

        $raw = (int)(boolean)$final . sprintf('%07b', (int)$opcode);

        $plLen = strlen($payload);
        if ($plLen <= 125) {
            $raw .= sprintf('%08b', $plLen);
        } elseif ($plLen <= 65535) {
            $raw .= sprintf('%08b', 126) . sprintf('%016b', $plLen);
        } else { // todo, make sure msg isn't longer than b1x71
            $raw .= sprintf('%08b', 127) . sprintf('%064b', $plLen);
        }

        $frame->addBuffer(static::encode($raw) . $payload);

        if ($mask) {
            // create masking key
            // insert it
            // mask the payload
        }

        return $frame;
    }

    /**
     * @param string of 1's and 0's
     * @return string
     */
    public static function encode($in) {
        if (strlen($in) > 8) {
            $out = '';

            while (strlen($in) >= 8) {
                $out .= static::encode(substr($in, 0, 8));
                $in   = substr($in, 8); 
            }

            return $out;
        }

        return chr(bindec($in));
    }

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

        return $this->_bytes_rec >= $payload_length + $payload_start;
    }

    /**
     * {@inheritdoc}
     */
    public function addBuffer($buf) {
        $buf = (string)$buf;

        $this->data      .= $buf;
        $this->_bytes_rec += strlen($buf);
    }

    /**
     * {@inheritdoc}
     */
    public function isFinal() {
        if ($this->_bytes_rec < 1) {
            throw new \UnderflowException('Not enough bytes received to determine if this is the final frame in message');
        }

        $fbb = sprintf('%08b', ord(substr($this->data, 0, 1)));

        return (boolean)(int)$fbb[0];
    }

    /**
     * {@inheritdoc}
     */
    public function isMasked() {
        if ($this->_bytes_rec < 2) {
            throw new \UnderflowException("Not enough bytes received ({$this->_bytes_rec}) to determine if mask is set");
        }

        return (boolean)bindec(substr(sprintf('%08b', ord(substr($this->data, 1, 1))), 0, 1));
    }

    /**
     * {@inheritdoc}
     */
    public function getOpcode() {
        if ($this->_bytes_rec < 1) {
            throw new \UnderflowException('Not enough bytes received to determine opcode');
        }

        return bindec(substr(sprintf('%08b', ord(substr($this->data, 0, 1))), 4, 4));
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

        return ord(substr($this->data, 1, 1)) & 127;
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
            $strings[] = ord(substr($this->data, $i, 1));
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

        return substr($this->data, $start, $length);
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
        $start   = $this->getPayloadStartingByte();

        if ($this->isMasked()) {
            $mask = $this->getMaskingKey();

            for ($i = 0; $i < $length; $i++) {
                // Double check the RFC - is the masking byte level or character level?
                $payload .= substr($this->data, $i + $start, 1) ^ substr($mask, $i % 4, 1);
            }
        } else {
            $payload = substr($this->data, $start, $this->getPayloadLength());
        }

        if (strlen($payload) !== $length) {
            // Is this possible?  isCoalesced() math _should_ ensure if there is mal-formed data, it would return false
            throw new \UnexpectedValueException('Payload length does not match expected length');
        }

        return $payload;
    }
}