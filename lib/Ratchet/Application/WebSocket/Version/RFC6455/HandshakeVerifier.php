<?php
namespace Ratchet\Application\WebSocket\Version\RFC6455;

/**
 * These are checks to ensure the client requested handshake are valid
 * Verification rules come from section 4.2.1 of the RFC6455 document
 * @todo Currently just returning invalid - should consider returning appropriate HTTP status code error #s
 */
class HandshakeVerifier {
    /**
     * Given an array of the headers this method will run through all verification methods
     * @param array
     * @return bool TRUE if all headers are valid, FALSE if 1 or more were invalid
     */
    public function verifyAll(array $headers) {
        $passes = 0;

        $passes += (int)$this->verifyMethod($headers['Request Method']);
        //$passes += (int)$this->verifyHTTPVersion($headers['???']); // This isn't in the array!
        $passes += (int)$this->verifyRequestURI($headers['Request Url']);
        $passes += (int)$this->verifyHost($headers['Host']);
        $passes += (int)$this->verifyUpgradeRequest($headers['Upgrade']);
        $passes += (int)$this->verifyConnection($headers['Connection']);
        $passes += (int)$this->verifyKey($headers['Sec-Websocket-Key']);
        //$passes += (int)$this->verifyVersion($headers['Sec-Websocket-Version']); // Temporarily breaking functionality

        return (6 === $passes);
    }

    /**
     * Test the HTTP method.  MUST be "GET"
     * @param string
     * @return bool
     * @todo Look into STD if "get" is valid (am I supposed to do case conversion?)
     */
    public function verifyMethod($val) {
        return ('GET' === $val);
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
     * @todo Implement this functionality
     */
    public function verifyRequestURI($val) {
        return true;
    }

    /**
     * @param string|null
     * @return bool
     * @todo Find out if I can find the master socket, ensure the port is attached to header if not 80 or 443 - not sure if this is possible, as I tried to hide it
     * @todo Once I fix HTTP::getHeaders just verify this isn't NULL or empty...or manybe need to verify it's a valid domin??? Or should it equal $_SERVER['HOST'] ?
     */
    public function verifyHost($val) {
        return (null !== $val);
    }

    /**
     * Verify the Upgrade request to WebSockets.
     * @param string MUST equal "websocket"
     * @return bool
     */
    public function verifyUpgradeRequest($val) {
        return ('websocket' === $val);
    }

    /**
     * Verify the Connection header
     * @param string MUST equal "Upgrade"
     * @return bool
     */
    public function verifyConnection($val) {
        if ('Upgrade' === $val) {
            return true;
        }

        $vals = explode(',', str_replace(', ', ',', $val));
        return (false !== array_search('Upgrade', $vals));
    }

    /**
     * This function verifyies the nonce is valid (64 big encoded, 16 bytes random string)
     * @param string|null
     * @return bool
     * @todo The spec says we don't need to base64_decode - can I just check if the length is 24 and not decode?
     */
    public function verifyKey($val) {
        return (16 === strlen(base64_decode((string)$val)));
    }

    /**
     * Verify Origin matches RFC6454 IF it is set
     * Origin is an optional field
     * @param string|null
     * @return bool
     * @todo Implement verification functality - see section 4.2.1.7
     */
    public function verifyOrigin($val) {
        if (null === $val) {
            return true;
        }

        // logic here
        return true;
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