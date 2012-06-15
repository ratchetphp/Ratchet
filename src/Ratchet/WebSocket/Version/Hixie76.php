<?php
namespace Ratchet\WebSocket\Version;
use Ratchet\ConnectionInterface;
use Ratchet\MessageInterface;
use Ratchet\WebSocket\Version\Hixie76\Connection;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Ratchet\WebSocket\Version\Hixie76\Frame;

/**
 * FOR THE LOVE OF BEER, PLEASE PLEASE PLEASE DON'T allow the use of this in your application!
 * Hixie76 is bad for 2 (there's more) reasons:
 *  1) The handshake is done in HTTP, which includes a key for signing in the body...
 *     BUT there is no Length defined in the header (as per HTTP spec) so the TCP buffer can't tell when the message is done!
 *  2) By nature it's insecure.  Google did a test study where they were able to do a
 *     man-in-the-middle attack on 10%-15% of the people who saw their ad who had a browser (currently only Safari) supporting the Hixie76 protocol.
 *     This was exploited by taking advantage of proxy servers in front of the user who ignored some HTTP headers in the handshake
 * The Hixie76 is currently implemented by Safari
 * @link http://tools.ietf.org/html/draft-hixie-thewebsocketprotocol-76
 */
class Hixie76 implements VersionInterface {
    /**
     * {@inheritdoc}
     */
    public function isProtocol(RequestInterface $request) {
        return !(null === $request->getHeader('Sec-WebSocket-Key2', true));
    }

    /**
     * {@inheritdoc}
     */
    public function getVersionNumber() {
        return 0;
    }

    /**
     * @param Guzzle\Http\Message\RequestInterface
     * @return Guzzle\Http\Message\Response
     */
    public function handshake(RequestInterface $request) {
        $body = $this->sign($request->getHeader('Sec-WebSocket-Key1', true), $request->getHeader('Sec-WebSocket-Key2', true), (string)$request->getBody());

        $headers = array(
            'Upgrade'                => 'WebSocket'
          , 'Connection'             => 'Upgrade'
          , 'Sec-WebSocket-Origin'   => $request->getHeader('Origin', true)
          , 'Sec-WebSocket-Location' => 'ws://' . $request->getHeader('Host', true) . $request->getPath()
        );

        $response = new Response(101, $headers, $body);
        $response->setStatus(101, 'WebSocket Protocol Handshake');

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function upgradeConnection(ConnectionInterface $conn, MessageInterface $coalescedCallback) {
        $upgraded = new Connection($conn);

        if (!isset($upgraded->WebSocket)) {
            $upgraded->WebSocket = new \StdClass;
        }

        $upgraded->WebSocket->coalescedCallback = $coalescedCallback;

        return $upgraded;
    }

    public function onMessage(ConnectionInterface $from, $data) {
        $overflow = '';

        if (!isset($from->WebSocket->frame)) {
            $from->WebSocket->frame = $this->newFrame();
        }

        $from->WebSocket->frame->addBuffer($data);
        if ($from->WebSocket->frame->isCoalesced()) {
            $overflow = $from->WebSocket->frame->extractOverflow();

            $parsed = $from->WebSocket->frame->getPayload();
            unset($from->WebSocket->frame);

            $from->WebSocket->coalescedCallback->onMessage($from, $parsed);

            unset($from->WebSocket->frame);
        }

        if (strlen($overflow) > 0) {
            $this->onMessage($from, $overflow);
        }
    }

    public function newFrame() {
        return new Frame;
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