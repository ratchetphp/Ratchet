<?php
namespace Ratchet\WebSocket\Version;
use Ratchet\ConnectionInterface;
use Ratchet\MessageInterface;
use Ratchet\WebSocket\Version\RFC6455\HandshakeVerifier;
use Ratchet\WebSocket\Version\RFC6455\Message;
use Ratchet\WebSocket\Version\RFC6455\Frame;
use Ratchet\WebSocket\Version\RFC6455\Connection;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;

/**
 * @link http://tools.ietf.org/html/rfc6455
 * @todo Unicode: return mb_convert_encoding(pack("N",$u), mb_internal_encoding(), 'UCS-4BE');
 */
class RFC6455 implements VersionInterface {
    const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    const UTF8_ACCEPT = 0;
    const UTF8_REJECT = 1;

    /**
     * Incremental UTF-8 validator with constant memory consumption (minimal state).
     *
     * Implements the algorithm "Flexible and Economical UTF-8 Decoder" by
     * Bjoern Hoehrmann (http://bjoern.hoehrmann.de/utf-8/decoder/dfa/).
     */
    public static $dfa = array(
        0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, # 00..1f
        0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, # 20..3f
        0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, # 40..5f
        0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, # 60..7f
        1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9, # 80..9f
        7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7, # a0..bf
        8,8,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2, # c0..df
        0xa,0x3,0x3,0x3,0x3,0x3,0x3,0x3,0x3,0x3,0x3,0x3,0x3,0x4,0x3,0x3, # e0..ef
        0xb,0x6,0x6,0x6,0x5,0x8,0x8,0x8,0x8,0x8,0x8,0x8,0x8,0x8,0x8,0x8, # f0..ff
        0x0,0x1,0x2,0x3,0x5,0x8,0x7,0x1,0x1,0x1,0x4,0x6,0x1,0x1,0x1,0x1, # s0..s0
        1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,1,1,0,1,0,1,1,1,1,1,1, # s1..s2
        1,2,1,1,1,1,1,2,1,2,1,1,1,1,1,1,1,1,1,1,1,1,1,2,1,1,1,1,1,1,1,1, # s3..s4
        1,2,1,1,1,1,1,1,1,2,1,1,1,1,1,1,1,1,1,1,1,1,1,3,1,3,1,1,1,1,1,1, # s5..s6
        1,3,1,1,1,1,1,3,1,3,1,1,1,1,1,1,1,3,1,1,1,1,1,1,1,1,1,1,1,1,1,1, # s7..s8
    );

    /**
     * @var RFC6455\HandshakeVerifier
     */
    protected $_verifier;

    /**
     * A lookup of the valid close codes that can be sent in a frame
     * @var array
     */
    private $closeCodes = array();

    public function __construct() {
        $this->_verifier = new HandshakeVerifier;
        $this->setCloseCodes();
    }

    /**
     * {@inheritdoc}
     */
    public function isProtocol(RequestInterface $request) {
        $version = (int)$request->getHeader('Sec-WebSocket-Version', -1);

        return ($this->getVersionNumber() === $version);
    }

    /**
     * {@inheritdoc}
     */
    public function getVersionNumber() {
        return 13;
    }

    /**
     * {@inheritdoc}
     */
    public function handshake(RequestInterface $request) {
        if (true !== $this->_verifier->verifyAll($request)) {
            return new Response(400);
        }

        return new Response(101, array(
            'Upgrade'              => 'websocket'
          , 'Connection'           => 'Upgrade'
          , 'Sec-WebSocket-Accept' => $this->sign($request->getHeader('Sec-WebSocket-Key'))
        ));
    }

    /**
     * @param Ratchet\ConnectionInterface
     * @return Ratchet\WebSocket\Version\RFC6455\Connection
     */
    public function upgradeConnection(ConnectionInterface $conn, MessageInterface $coalescedCallback) {
        $upgraded = new Connection($conn);

        if (!isset($upgraded->WebSocket)) {
            $upgraded->WebSocket = new \StdClass;
        }

        $upgraded->WebSocket->coalescedCallback = $coalescedCallback;

        return $upgraded;
    }

    /**
     * @param Ratchet\WebSocket\Version\RFC6455\Connection
     * @param string
     */
    public function onMessage(ConnectionInterface $from, $data) {
        $overflow = '';

        if (!isset($from->WebSocket->message)) {
            $from->WebSocket->message = $this->newMessage();
        }

        // There is a frame fragment attatched to the connection, add to it
        if (!isset($from->WebSocket->frame)) {
            $from->WebSocket->frame = $this->newFrame();
        }

        $from->WebSocket->frame->addBuffer($data);
        if ($from->WebSocket->frame->isCoalesced()) {
            $frame = $from->WebSocket->frame;

            if (false !== $frame->getRsv1() ||
                false !== $frame->getRsv2() ||
                false !== $frame->getRsv3()
            ) {
                return $from->close($frame::CLOSE_PROTOCOL);
            }

            if (!$frame->isMasked()) {
                return $from->close($frame::CLOSE_PROTOCOL);
            }

            $opcode = $frame->getOpcode();

            if ($opcode > 2) {
                if ($frame->getPayloadLength() > 125 || !$frame->isFinal()) {
                    return $from->close($frame::CLOSE_PROTOCOL);
                }

                switch ($opcode) {
                    case $frame::OP_CLOSE:
                        $closeCode = 0;

                        $bin = $frame->getPayload();

                        if (empty($bin)) {
                            return $from->close();
                        }

                        if (strlen($bin) >= 2) {
                            list($closeCode) = array_merge(unpack('n*', substr($bin, 0, 2)));
                        }

                        if (!$this->isValidCloseCode($closeCode)) {
                            return $from->close($frame::CLOSE_PROTOCOL);
                        }

                        if (!$this->isUtf8(substr($bin, 2))) {
                            return $from->close($frame::CLOSE_BAD_PAYLOAD);
                        }

                        return $from->close($frame);
                    break;
                    case $frame::OP_PING:
                        $from->send($this->newFrame($frame->getPayload(), true, $frame::OP_PONG));
                    break;
                    case $frame::OP_PONG:
                    break;
                    default:
                        return $from->close($frame::CLOSE_PROTOCOL);
                    break;
                }

                $overflow = $from->WebSocket->frame->extractOverflow();

                unset($from->WebSocket->frame, $frame, $opcode);

                if (strlen($overflow) > 0) {
                    $this->onMessage($from, $overflow);
                }

                return;
            }

            $overflow = $from->WebSocket->frame->extractOverflow();

            if ($frame::OP_CONTINUE == $frame->getOpcode() && 0 == count($from->WebSocket->message)) {
                return $from->close($frame::CLOSE_PROTOCOL);
            }

            if (count($from->WebSocket->message) > 0 && $frame::OP_CONTINUE != $frame->getOpcode()) {
                return $from->close($frame::CLOSE_PROTOCOL);
            }

            $from->WebSocket->message->addFrame($from->WebSocket->frame);
            unset($from->WebSocket->frame);
        }

        if ($from->WebSocket->message->isCoalesced()) {
            $parsed = $from->WebSocket->message->getPayload();
            unset($from->WebSocket->message);

            if (!$this->isUtf8($parsed)) {
                return $from->close(Frame::CLOSE_BAD_PAYLOAD);
            }

            $from->WebSocket->coalescedCallback->onMessage($from, $parsed);
        }

        if (strlen($overflow) > 0) {
            $this->onMessage($from, $overflow);
        }
    }

    /**
     * @return RFC6455\Message
     */
    public function newMessage() {
        return new Message;
    }

    /**
     * @return RFC6455\Frame
     */
    public function newFrame($payload = null, $final = true, $opcode = 1) {
        return new Frame($payload, $final, $opcode);
    }

    /**
     * @todo This is needed when a client is created - needs re-write as missing parts of protocol
     * @param string
     * @return string
     */
    public function frame($message, $mask = true) {
        return $this->newFrame($message)->getContents();
    }

    /**
     * Used when doing the handshake to encode the key, verifying client/server are speaking the same language
     * @param string
     * @return string
     * @internal
     */
    public function sign($key) {
        return base64_encode(sha1($key . static::GUID, true));
    }

    /**
     * Determine if a close code is valid
     * @param int|string
     * @return bool
     */
    public function isValidCloseCode($val) {
        if (array_key_exists($val, $this->closeCodes)) {
            return true;
        }

        if ($val >= 3000 && $val <= 4999) {
            return true;
        }

        return false;
    }

    /**
     * Creates a private lookup of valid, private close codes
     */
    protected function setCloseCodes() {
        $this->closeCodes[Frame::CLOSE_NORMAL]      = true;
        $this->closeCodes[Frame::CLOSE_GOING_AWAY]  = true;
        $this->closeCodes[Frame::CLOSE_PROTOCOL]    = true;
        $this->closeCodes[Frame::CLOSE_BAD_DATA]    = true;
        //$this->closeCodes[Frame::CLOSE_NO_STATUS]   = true;
        //$this->closeCodes[Frame::CLOSE_ABNORMAL]    = true;
        $this->closeCodes[Frame::CLOSE_BAD_PAYLOAD] = true;
        $this->closeCodes[Frame::CLOSE_POLICY]      = true;
        $this->closeCodes[Frame::CLOSE_TOO_BIG]     = true;
        $this->closeCodes[Frame::CLOSE_MAND_EXT]    = true;
        $this->closeCodes[Frame::CLOSE_SRV_ERR]     = true;
        //$this->closeCodes[Frame::CLOSE_TLS]         = true;
    }

    /**
     * Determine if a string is a valid UTF-8 string
     * @param string
     * @return bool
     */
    function isUtf8($str) {
        if (false === mb_check_encoding($str, 'UTF-8')) {
            return false;
        }

        $len = strlen($str);

        // The secondary method of checking is painfully slow
        // If the message is more than 10kb, skip UTF-8 checks
        if ($len > 10000) {
            return true;
        }

        $state = static::UTF8_ACCEPT;

        for ($i = 0; $i < $len; $i++) {
            $state = static::$dfa[256 + ($state << 4) + static::$dfa[ord($str[$i])]];

            if (static::UTF8_REJECT === $state) {
                return false;
            }
        }

        return true;
    }
}