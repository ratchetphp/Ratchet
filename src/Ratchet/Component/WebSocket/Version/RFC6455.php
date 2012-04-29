<?php
namespace Ratchet\Component\WebSocket\Version;
use Ratchet\Component\WebSocket\Version\RFC6455\HandshakeVerifier;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;


/**
 * @link http://tools.ietf.org/html/rfc6455
 */
class RFC6455 implements VersionInterface {
    const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    /**
     * @var RFC6455\HandshakeVerifier
     */
    protected $_verifier;

    public function __construct() {
        $this->_verifier = new HandshakeVerifier;
    }

    /**
     * {@inheritdoc}
     */
    public static function isProtocol(RequestInterface $request) {
        $version = (int)$request->getHeader('Sec-WebSocket-Version', -1);
        return (13 === $version);
    }

    /**
     * {@inheritdoc}
     * @todo Decide what to do on failure...currently throwing an exception and I think socket connection is closed.  Should be sending 40x error - but from where?
     */
    public function handshake(RequestInterface $request) {
        if (true !== $this->_verifier->verifyAll($request)) {
            throw new \InvalidArgumentException('Invalid HTTP header');
        }

        $headers = array(
            'Upgrade'              => 'websocket'
          , 'Connection'           => 'Upgrade'
          , 'Sec-WebSocket-Accept' => $this->sign($request->getHeader('Sec-WebSocket-Key'))
        );

        return new Response('101', $headers);
    }

    /**
     * @return RFC6455\Message
     */
    public function newMessage() {
        return new RFC6455\Message;
    }

    /**
     * @return RFC6455\Frame
     */
    public function newFrame() {
        return new RFC6455\Frame;
    }

    /**
     * Thanks to @lemmingzshadow for the code on decoding a HyBi-10 frame
     * @link https://github.com/lemmingzshadow/php-websocket
     * @todo look into what happens when false is returned here
     * @todo This is needed when a client is created - needs re-write as missing parts of protocol
     * @param string
     * @return string
     */
    public function frame($message, $mask = true) {
        $payload = $message;
        $type    = 'text';
        $masked  = $mask;

        $frameHead = array();
        $frame = '';
        $payloadLength = strlen($payload);

        switch($type) {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
            break;

            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
            break;

            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
            break;

            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
            break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for($i = 0; $i < 8; $i++) {
                $frameHead[$i+2] = bindec($payloadLengthBin[$i]);
            }
            // most significant bit MUST be 0 (return false if to much data)
            if($frameHead[2] > 127) {
                return false;
            }
        } elseif($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // convert frame-head to string:
        foreach(array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        } if($masked === true) {
            // generate a random mask:
            $mask = array();
            for($i = 0; $i < 4; $i++)
            {
                $mask[$i] = chr(rand(0, 255));
            }

            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);

        // append payload to frame:
        $framePayload = array();
        for($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }

    /**
     * Used when doing the handshake to encode the key, verifying client/server are speaking the same language
     * @param string
     * @return string
     * @internal
     */
    public function sign($key) {
        return base64_encode(sha1($key . static::GUID, 1));
    }
}