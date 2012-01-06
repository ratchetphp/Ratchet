<?php
namespace Ratchet\Application\WebSocket\Version;
use Guzzle\Http\Message\RequestInterface;

/**
 * FOR THE LOVE OF BEER, PLEASE PLEASE PLEASE DON'T allow the use of this in your application!
 * Hixie76 is bad for 2 (there's more) reasons:
 *  1) The handshake is done in HTTP, which includes a key for signing in the body...
 *     BUT there is no Length defined in the header (as per HTTP spec) so the TCP buffer can't tell when the message is done!
 *  2) By nature it's insecure.  Google did a test study where they were able to do a
 *     man-in-the-middle attack on 10%-15% of the people who saw their ad who had a browser (currently only Safari) supporting the Hixie76 protocol.
 *     This was exploited by taking advantage of proxy servers in front of the user who ignored some HTTP headers in the handshake
 * The Hixie76 is currently implemented by Safari
 * Handshake from Andrea Giammarchi (http://webreflection.blogspot.com/2010/06/websocket-handshake-76-simplified.html)
 * @link http://tools.ietf.org/html/draft-hixie-thewebsocketprotocol-76
 */
class Hixie76 implements VersionInterface {
    public static function isProtocol(RequestInterface $request) {
        return !(null === $request->getHeader('Sec-WebSocket-Key2'));
    }

    /**
     * @param string
     * @return string
     */
    public function handshake(RequestInterface $request) {
        $message = $request->getRawHeaders() . $request->getResponse()->getBody(true);

        $buffer   = $message;
        $resource = $host = $origin = $key1 = $key2 = $protocol = $code = $handshake = null;

        preg_match('#GET (.*?) HTTP#', $buffer, $match) && $resource = $match[1];
        preg_match("#Host: (.*?)\r\n#", $buffer, $match) && $host = $match[1];
        preg_match("#Sec-WebSocket-Key1: (.*?)\r\n#", $buffer, $match) && $key1 = $match[1];
        preg_match("#Sec-WebSocket-Key2: (.*?)\r\n#", $buffer, $match) && $key2 = $match[1];
        preg_match("#Sec-WebSocket-Protocol: (.*?)\r\n#", $buffer, $match) && $protocol = $match[1];
        preg_match("#Origin: (.*?)\r\n#", $buffer, $match) && $origin = $match[1];
        preg_match("#\r\n(.*?)\$#", $buffer, $match) && $code = $match[1];

        return "HTTP/1.1 101 WebSocket Protocol Handshake\r\n".
            "Upgrade: WebSocket\r\n"
          . "Connection: Upgrade\r\n"
          . "Sec-WebSocket-Origin: {$origin}\r\n"
          . "Sec-WebSocket-Location: ws://{$host}{$resource}\r\n"
          . ($protocol ? "Sec-WebSocket-Protocol: {$protocol}\r\n" : "")
          . "\r\n"
          . $this->_createHandshakeThingy($key1, $key2, $code)
        ;
    }

    public function newMessage() {
        return new Hixie76\Message;
    }

    public function newFrame() {
        return new Hixie76\Frame;
    }

    public function frame($message, $mask = true) {
        return chr(0) . $message . chr(255);
    }

    protected function _doStuffToObtainAnInt32($key) {
        return preg_match_all('#[0-9]#', $key, $number) && preg_match_all('# #', $key, $space) ?
            implode('', $number[0]) / count($space[0]) :
            ''
        ;
    }

    protected function _createHandshakeThingy($key1, $key2, $code) {
        return md5(
            pack('N', $this->_doStuffToObtainAnInt32($key1))
          . pack('N', $this->_doStuffToObtainAnInt32($key2))
          . $code
        , true);
    }
}