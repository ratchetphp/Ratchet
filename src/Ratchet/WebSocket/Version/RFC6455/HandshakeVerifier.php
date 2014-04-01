<?php
namespace Ratchet\WebSocket\Version\RFC6455;
use Guzzle\Http\Message\RequestInterface;

/**
 * These are checks to ensure the client requested handshake are valid
 * Verification rules come from section 4.2.1 of the RFC6455 document
 * @todo Currently just returning invalid - should consider returning appropriate HTTP status code error #s
 */
class HandshakeVerifier {
    /**
     * Given an array of the headers this method will run through all verification methods
     * @param \Guzzle\Http\Message\RequestInterface $request
     * @return bool TRUE if all headers are valid, FALSE if 1 or more were invalid
     */
    public function verifyAll(RequestInterface $request) {
        $passes = 0;

        $passes += (int)$this->verifyMethod($request->getMethod());
        $passes += (int)$this->verifyHTTPVersion($request->getProtocolVersion());
        $passes += (int)$this->verifyRequestURI($request->getPath());
        $passes += (int)$this->verifyHost((string)$request->getHeader('Host'));
        $passes += (int)$this->verifyUpgradeRequest((string)$request->getHeader('Upgrade'));
        $passes += (int)$this->verifyConnection((string)$request->getHeader('Connection'));
        $passes += (int)$this->verifyKey((string)$request->getHeader('Sec-WebSocket-Key'));
        //$passes += (int)$this->verifyVersion($headers['Sec-WebSocket-Version']); // Temporarily breaking functionality

        return (7 === $passes);
    }

    /**
     * Test the HTTP method.  MUST be "GET"
     * @param string
     * @return bool
     */
    public function verifyMethod($val) {
        return ('get' === strtolower($val));
    }

    /**
     * Test the HTTP version passed.  MUST be 1.1 or greater
     * @param string|int
     * @return bool
     */
    public function verifyHTTPVersion($val) {
        return (1.1 <= (double)$val);
    }

    /**
     * @param string
     * @return bool
     */
    public function verifyRequestURI($val) {
        if ($val[0] != '/') {
            return false;
        }

        if (false !== strstr($val, '#')) {
            return false;
        }

        if (!extension_loaded('mbstring')) {
            return true;
        }

        return mb_check_encoding($val, 'US-ASCII');
    }

    /**
     * @param string|null
     * @return bool
     * @todo Find out if I can find the master socket, ensure the port is attached to header if not 80 or 443 - not sure if this is possible, as I tried to hide it
     * @todo Once I fix HTTP::getHeaders just verify this isn't NULL or empty...or maybe need to verify it's a valid domain??? Or should it equal $_SERVER['HOST'] ?
     */
    public function verifyHost($val) {
        return (null !== $val);
    }

    /**
     * Verify the Upgrade request to WebSockets.
     * @param  string $val MUST equal "websocket"
     * @return bool
     */
    public function verifyUpgradeRequest($val) {
        return ('websocket' === strtolower($val));
    }

    /**
     * Verify the Connection header
     * @param  string $val MUST equal "Upgrade"
     * @return bool
     */
    public function verifyConnection($val) {
        $val = strtolower($val);

        if ('upgrade' === $val) {
            return true;
        }

        $vals = explode(',', str_replace(', ', ',', $val));

        return (false !== array_search('upgrade', $vals));
    }

    /**
     * This function verifies the nonce is valid (64 big encoded, 16 bytes random string)
     * @param string|null
     * @return bool
     * @todo The spec says we don't need to base64_decode - can I just check if the length is 24 and not decode?
     * @todo Check the spec to see what the encoding of the key could be
     */
    public function verifyKey($val) {
        return (16 === strlen(base64_decode((string)$val)));
    }

    /**
     * Verify the version passed matches this RFC
     * @param string|int MUST equal 13|"13"
     * @return bool
     * @todo Ran in to a problem here...I'm having HyBi use the RFC files, this breaks it!  oops
     */
    public function verifyVersion($val) {
        return (13 === (int)$val);
    }

    /**
     * @todo Write logic for this method.  See section 4.2.1.8
     */
    public function verifyProtocol($val) {
    }

    /**
     * @todo Write logic for this method.  See section 4.2.1.9
     */
    public function verifyExtensions($val) {
    }
}
