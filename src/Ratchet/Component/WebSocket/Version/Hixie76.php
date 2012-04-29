<?php
namespace Ratchet\Component\WebSocket\Version;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;

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
    /**
     * {@inheritdoc}
     */
    public static function isProtocol(RequestInterface $request) {
        return !(null === $request->getHeader('Sec-WebSocket-Key2'));
    }

    /**
     * @param string
     * @return string
     */
    public function handshake(RequestInterface $request) {
        $body = $this->sign($request->getHeader('Sec-WebSocket-Key1'), $request->getHeader('Sec-WebSocket-Key2'), $request->getBody());

        $headers = array(
            'Upgrade'                => 'WebSocket'
          , 'Connection'             => 'Upgrade'
          , 'Sec-WebSocket-Origin'   => $request->getHeader('Origin')
          , 'Sec-WebSocket-Location' => 'ws://' . $request->getHeader('Host') . $request->getPath()
        );

        $response = new Response('101', $headers, $body);
        $response->setStatus('101', 'WebSocket Protocol Handshake');

        return $response;
    }

    /**
     * @return Hixie76\Message
     */
    public function newMessage() {
        return new Hixie76\Message;
    }

    /**
     * @return Hixie76\Frame
     */
    public function newFrame() {
        return new Hixie76\Frame;
    }

    /**
     * {@inheritdoc}
     */
    public function frame($message, $mask = true) {
        return chr(0) . $message . chr(255);
    }

    public function generateKeyNumber($key) {
        if (0 === substr_count($key, ' ')) {
            return '';
        }

        $int = (int)preg_replace('[\D]', '', $key) / substr_count($key, ' ');

        return (is_int($int)) ? $int : '';
    }

    protected function sign($key1, $key2, $code) {
        return md5(
            pack('N', $this->generateKeyNumber($key1))
          . pack('N', $this->generateKeyNumber($key2))
          . $code
        , true);
    }
}