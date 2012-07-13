<?php
namespace Ratchet\WebSocket\Version\RFC6455;
use Ratchet\WebSocket\Version\FrameInterface;

class Frame implements FrameInterface {
    const OP_CONTINUE =  0;
    const OP_TEXT     =  1;
    const OP_BINARY   =  2;
    const OP_CLOSE    =  8;
    const OP_PING     =  9;
    const OP_PONG     = 10;

    const CLOSE_NORMAL      = 1000;
    const CLOSE_GOING_AWAY  = 1001;
    const CLOSE_PROTOCOL    = 1002;
    const CLOSE_BAD_DATA    = 1003;
    const CLOSE_NO_STATUS   = 1005;
    const CLOSE_ABNORMAL    = 1006;
    const CLOSE_BAD_PAYLOAD = 1007;
    const CLOSE_POLICY      = 1008;
    const CLOSE_TOO_BIG     = 1009;
    const CLOSE_MAND_EXT    = 1010;
    const CLOSE_SRV_ERR     = 1011;
    const CLOSE_TLS         = 1015;

    const MASK_LENGTH = 4;

    /**
     * The contents of the frame
     * @var string
     */
    protected $data = '';

    /**
     * Number of bytes received from the frame
     * @var int
     */
    public $bytesRecvd = 0;

    /**
     * Number of bytes in the payload (as per framing protocol)
     * @var int
     */
    protected $_pay_len_def = -1;

    public function __construct($payload = null, $final = true, $opcode = 1) {
        if (null === $payload) {
            return;
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

        $this->addBuffer(static::encode($raw) . $payload);
    }

    /**
     * @param string A valid UTF-8 string to send over the wire
     * @param bool Is the final frame in a message
     * @param int The opcode of the frame, see constants
     * @param bool Mask the payload
     * @return Frame
     * @throws InvalidArgumentException If the payload is not a valid UTF-8 string
     * @throws LengthException If the payload is too big
     */
    public static function create($payload, $final = true, $opcode = 1) {
        return new static($payload, $final, $opcode);
    }

    /**
     * Encode the fake binary string to send over the wire
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

        return $this->bytesRecvd >= $payload_length + $payload_start;
    }

    /**
     * {@inheritdoc}
     */
    public function addBuffer($buf) {
        $this->data       .= $buf;
        $this->bytesRecvd += strlen($buf);
    }

    /**
     * {@inheritdoc}
     */
    public function isFinal() {
        if ($this->bytesRecvd < 1) {
            throw new \UnderflowException('Not enough bytes received to determine if this is the final frame in message');
        }

        return 128 === (ord($this->data[0]) & 128);
    }

    /**
     * @return boolean
     * @throws UnderflowException
     */
    public function getRsv1() {
        if ($this->bytesRecvd < 1) {
            throw new \UnderflowException('Not enough bytes received to determine reserved bit');
        }

        return 64 === (ord($this->data[0]) & 64);
    }

    /**
     * @return boolean
     * @throws UnderflowException
     */
    public function getRsv2() {
        if ($this->bytesRecvd < 1) {
            throw new \UnderflowException('Not enough bytes received to determine reserved bit');
        }

        return 32 === (ord($this->data[0]) & 32);
    }

    /**
     * @return boolean
     * @throws UnderflowException
     */
    public function getRsv3() {
        if ($this->bytesRecvd < 1) {
            throw new \UnderflowException('Not enough bytes received to determine reserved bit');
        }

        return 16 == (ord($this->data[0]) & 16);
    }

    /**
     * {@inheritdoc}
     */
    public function isMasked() {
        if ($this->bytesRecvd < 2) {
            throw new \UnderflowException("Not enough bytes received ({$this->bytesRecvd}) to determine if mask is set");
        }

        return 128 === (ord($this->data[1]) & 128);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskingKey() {
        if (!$this->isMasked()) {
            return '';
        }

        $start  = 1 + $this->getNumPayloadBytes();

        if ($this->bytesRecvd < $start + static::MASK_LENGTH) {
            throw new \UnderflowException('Not enough data buffered to calculate the masking key');
        }

        return substr($this->data, $start, static::MASK_LENGTH);
    }

    /**
     * Create a 4 byte masking key
     * @return string
     */
    public function generateMaskingKey() {
        $mask = '';

        for ($i = 1; $i <= static::MASK_LENGTH; $i++) {
            $mask .= chr(rand(32, 126));
        }

        return $mask;
    }

    /**
     * Apply a mask to the payload
     * @param string|null If NULL is passed a masking key will be generated
     * @throws InvalidArgumentException If there is an issue with the given masking key
     * @throws UnderflowException If the frame is not coalesced
     */
    public function maskPayload($maskingKey = null) {
        if (null === $maskingKey) {
            $maskingKey = $this->generateMaskingKey();
        }

        if (static::MASK_LENGTH !== strlen($maskingKey)) {
            throw new \InvalidArgumentException("Masking key must be " . static::MASK_LENGTH ." characters");
        }

        if (extension_loaded('mbstring') && true !== mb_check_encoding($maskingKey, 'US-ASCII')) {
            throw new \OutOfBoundsException("Masking key MUST be ASCII");
        }

        $this->unMaskPayload();

        $byte = sprintf('%08b', ord($this->data[1]));

        $this->data = substr_replace($this->data, static::encode(substr_replace($byte, '1', 0, 1)), 1, 1);
        $this->data = substr_replace($this->data, $maskingKey, $this->getNumPayloadBytes() + 1, 0);

        $this->bytesRecvd += static::MASK_LENGTH;
        $this->data        = substr_replace($this->data, $this->applyMask($maskingKey), $this->getPayloadStartingByte(), $this->getPayloadLength());

        return $this;
    }

    /**
     * Remove a mask from the payload
     * @throws UnderFlowException If the frame is not coalesced
     * @return Frame
     */
    public function unMaskPayload() {
        if (!$this->isMasked()) {
            return $this;
        }

        $maskingKey = $this->getMaskingKey();

        $byte = sprintf('%08b', ord($this->data[1]));

        $this->data = substr_replace($this->data, static::encode(substr_replace($byte, '0', 0, 1)), 1, 1);
        $this->data = substr_replace($this->data, '', $this->getNumPayloadBytes() + 1, static::MASK_LENGTH);

        $this->bytesRecvd -= static::MASK_LENGTH;
        $this->data        = substr_replace($this->data, $this->applyMask($maskingKey), $this->getPayloadStartingByte(), $this->getPayloadLength());

        return $this;
    }

    /**
     * Apply a mask to a string or the payload of the instance
     * @param string The 4 character masking key to be applied
     * @param string|null A string to mask or null to use the payload
     * @throws UnderflowException If using the payload but enough hasn't been buffered
     * @return string The masked string
     */
    protected function applyMask($maskingKey, $payload = null) {
        if (null === $payload) {
            if (!$this->isCoalesced()) {
                throw new \UnderflowException('Frame must be coalesced to apply a mask');
            }

            $payload = substr($this->data, $this->getPayloadStartingByte(), $this->getPayloadLength());
        }

        $applied = '';
        for ($i = 0, $len = strlen($payload); $i < $len; $i++) {
            $applied .= $payload[$i] ^ $maskingKey[$i % static::MASK_LENGTH];
        }

        return $applied;
    }

    /**
     * {@inheritdoc}
     */
    public function getOpcode() {
        if ($this->bytesRecvd < 1) {
            throw new \UnderflowException('Not enough bytes received to determine opcode');
        }

        return bindec(substr(sprintf('%08b', ord($this->data[0])), 4, 4));
    }

    /**
     * Gets the decimal value of bits 9 (10th) through 15 inclusive
     * @return int
     * @throws UnderflowException If the buffer doesn't have enough data to determine this
     */
    protected function getFirstPayloadVal() {
        if ($this->bytesRecvd < 2) {
            throw new \UnderflowException('Not enough bytes received');
        }

        return ord($this->data[1]) & 127;
    }

    /**
     * @return int (7|23|71) Number of bits defined for the payload length in the fame
     * @throws UnderflowException
     */
    protected function getNumPayloadBits() {
        if ($this->bytesRecvd < 2) {
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
        // Note: The documentation specifies the length is to be 63 bits, but I think that's a typo and is 64 (16+48)
        if ($check === 127) {
            $bits += 48;
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
        if ($this->bytesRecvd < 1 + $byte_length) {
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
    public function getPayloadStartingByte() {
        return 1 + $this->getNumPayloadBytes() + strlen($this->getMaskingKey());
    }

    /**
     * {@inheritdoc}
     * @todo Consider not checking mask, always returning the payload, masked or not
     */
    public function getPayload() {
        if (!$this->isCoalesced()) {
            throw new \UnderflowException('Can not return partial message');
        }

        if ($this->isMasked()) {
            $payload = $this->applyMask($this->getMaskingKey());
        } else {
            $payload = substr($this->data, $this->getPayloadStartingByte(), $this->getPayloadLength());
        }

        return $payload;
    }

    /**
     * Get the raw contents of the frame
     * @todo This is untested, make sure the substr is right - trying to return the frame w/o the overflow
     */
    public function getContents() {
        return substr($this->data, 0, $this->getPayloadStartingByte() + $this->getPayloadLength());
    }

    /**
     * Sometimes clients will concatinate more than one frame over the wire
     * This method will take the extra bytes off the end and return them
     * @todo Consider returning new Frame
     * @return string
     */
    public function extractOverflow() {
        if ($this->isCoalesced()) {
            $endPoint  = $this->getPayloadLength();
            $endPoint += $this->getPayloadStartingByte();

            if ($this->bytesRecvd > $endPoint) {
                $overflow   = substr($this->data, $endPoint);
                $this->data = substr($this->data, 0, $endPoint);

                return $overflow;
            }
        }

        return '';
    }
}