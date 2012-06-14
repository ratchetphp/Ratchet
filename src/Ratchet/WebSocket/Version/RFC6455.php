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
     * @todo Decide what to do on failure...currently throwing an exception and I think socket connection is closed.  Should be sending 40x error - but from where?
     */
    public function handshake(RequestInterface $request) {
        if (true !== $this->_verifier->verifyAll($request)) {
            // new header with 4xx error message

            throw new \InvalidArgumentException('Invalid HTTP header');
        }

        $headers = array(
            'Upgrade'              => 'websocket'
          , 'Connection'           => 'Upgrade'
          , 'Sec-WebSocket-Accept' => $this->sign($request->getHeader('Sec-WebSocket-Key'))
          , 'X-Powered-By'         => \Ratchet\VERSION
        );

        return new Response(101, $headers);
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

            if (!$frame->isMasked()) {
                unset($from->WebSocket->frame);

                $from->send($this->newFrame($frame::CLOSE_PROTOCOL, true, $frame::OP_CLOSE));
                $from->getConnection()->close();

                return;
            }

            $opcode = $frame->getOpcode();

            if ($opcode > 2) {
                switch ($opcode) {
                    case $frame::OP_CLOSE:
                        $from->send($frame->unMaskPayload());
                        $from->getConnection()->close();
//                        $from->send(Frame::create(Frame::CLOSE_NORMAL, true, Frame::OP_CLOSE));

                        return;
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

            $from->WebSocket->message->addFrame($from->WebSocket->frame);
            unset($from->WebSocket->frame);
        }

        if ($from->WebSocket->message->isCoalesced()) {
            $parsed = $from->WebSocket->message->getPayload();
            unset($from->WebSocket->message);

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
}