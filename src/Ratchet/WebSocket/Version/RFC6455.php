<?php
namespace Ratchet\WebSocket\Version;
use Ratchet\WebSocket\Version\RFC6455\HandshakeVerifier;
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

    public function getVersionNumber() {
        return 13;
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

        return new Response(101, $headers);
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
     * @todo This is needed when a client is created - needs re-write as missing parts of protocol
     * @param string
     * @return string
     */
    public function frame($message, $mask = true) {
        return RFC6455\Frame::create($message)->data;
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