<?php
namespace Ratchet\WebSocket\Version;
use Ratchet\WebSocket\Version\RFC6455;
use Ratchet\WebSocket\Version\RFC6455\Frame;
use Guzzle\Http\Message\RequestFactory;
use Guzzle\Http\Message\EntityEnclosingRequest;

/**
 * @covers Ratchet\WebSocket\Version\RFC6455
 */
class RFC6455Test extends \PHPUnit_Framework_TestCase {
    protected $version;

    public function setUp() {
        $this->version = new RFC6455;
    }

    /**
     * @dataProvider handshakeProvider
     */
    public function testKeySigningForHandshake($key, $accept) {
        $this->assertEquals($accept, $this->version->sign($key));
    }

    public static function handshakeProvider() {
        return array(
            array('x3JJHMbDL1EzLkh9GBhXDw==', 'HSmrc0sMlYUkAGmm5OPpG2HaGWk=')
          , array('dGhlIHNhbXBsZSBub25jZQ==', 's3pPLMBiTxaQ9kYGzzhZRbK+xOo=')
        );
    }

    /**
     * @dataProvider UnframeMessageProvider
     */
    public function testUnframeMessage($message, $framed) {
        $frame = new Frame;
        $frame->addBuffer(base64_decode($framed));

        $this->assertEquals($message, $frame->getPayload());
    }

    public static function UnframeMessageProvider() {
        return array(
            array('Hello World!',                'gYydAIfa1WXrtvIg0LXvbOP7')
          , array('!@#$%^&*()-=_+[]{}\|/.,<>`~', 'gZv+h96r38f9j9vZ+IHWrvOWoayF9oX6gtfRqfKXwOeg')
          , array('ಠ_ಠ',                         'gYfnSpu5B/g75gf4Ow==')
          , array("The quick brown fox jumps over the lazy dog.  All work and no play makes Chris a dull boy.  I'm trying to get past 128 characters for a unit test here...", 'gf4Amahb14P8M7Kj2S6+4MN7tfHHLLmjzjSvo8IuuvPbe7j1zSn398A+9+/JIa6jzDSwrYh7lu/Ee6Ds2jD34sY/9+3He6fvySL37skwsvCIGL/xwSj34og/ou/Ee7Xs0XX3o+F8uqPcKa7qxjz398d7sObce6fi2y/3sppj9+DAOqXiyy+y8dt7sezae7aj3TW+94gvsvDce7/m2j75rYY=')
        );
    }

    public function testUnframeMatchesPreFraming() {
        $string = 'Hello World!';
        $framed = $this->version->newFrame($string)->getContents();

        $frame = new Frame;
        $frame->addBuffer($framed);

        $this->assertEquals($string, $frame->getPayload());
    }

    public static $good_rest = 'GET /chat HTTP/1.1';

    public static $good_header = array(
        'Host'                   => 'server.example.com'
      , 'Upgrade'                => 'websocket'
      , 'Connection'             => 'Upgrade'
      , 'Sec-WebSocket-Key'      => 'dGhlIHNhbXBsZSBub25jZQ=='
      , 'Origin'                 => 'http://example.com'
      , 'Sec-WebSocket-Protocol' => 'chat, superchat'
      , 'Sec-WebSocket-Version'  => 13
    );

    public function caseVariantProvider() {
        return array(
            array('Sec-Websocket-Version')
          , array('sec-websocket-version')
          , array('SEC-WEBSOCKET-VERSION')
          , array('sEC-wEBsOCKET-vERSION')
        );
    }

    /**
     * @dataProvider caseVariantProvider
     */
    public function testIsProtocolWithCaseInsensitivity($headerName) {
        $header = static::$good_header;
        unset($header['Sec-WebSocket-Version']);
        $header[$headerName] = 13;

        $this->assertTrue($this->version->isProtocol(new EntityEnclosingRequest('get', '/', $header)));
    }

    /**
     * A helper function to try and quickly put together a valid WebSocket HTTP handshake
     * but optionally replace a piece to an invalid value for failure testing
     */
    public static function getAndSpliceHeader($key = null, $val = null) {
        $headers = static::$good_header;

        if (null !== $key && null !== $val) {
            $headers[$key] = $val;
        }

        $header = '';
        foreach ($headers as $key => $val) {
            if (!empty($key)) {
                $header .= "{$key}: ";
            }

            $header .= "{$val}\r\n";
        }
        $header .= "\r\n";

        return $header;
    }

    public static function headerHandshakeProvider() {
        return array(
            array(false, "GET /test HTTP/1.0\r\n" . static::getAndSpliceHeader())
          , array(true,  static::$good_rest . "\r\n" . static::getAndSpliceHeader())
          , array(false, "POST / HTTP:/1.1\r\n" . static::getAndSpliceHeader())
          , array(false, static::$good_rest . "\r\n" . static::getAndSpliceHeader('Upgrade', 'useless'))
          , array(false, "GET /ಠ_ಠ HTTP/1.1\r\n" . static::getAndSpliceHeader())
          , array(true, static::$good_rest . "\r\n" . static::getAndSpliceHeader('Connection', 'Herp, Upgrade, Derp'))
        );
    }

    /**
     * @dataProvider headerHandshakeProvider
     */
    public function testVariousHeadersToCheckHandshakeTolerance($pass, $header) {
        $request  = RequestFactory::getInstance()->fromMessage($header);
        $response = $this->version->handshake($request);

        $this->assertInstanceOf('\\Guzzle\\Http\\Message\\Response', $response);

        if ($pass) {
            $this->assertEquals(101, $response->getStatusCode());
        } else {
            $this->assertGreaterThanOrEqual(400, $response->getStatusCode());
        }
    }

    public function testNewMessage() {
        $this->assertInstanceOf('\\Ratchet\\WebSocket\\Version\\RFC6455\\Message', $this->version->newMessage());
    }

    public function testNewFrame() {
        $this->assertInstanceOf('\\Ratchet\\WebSocket\\Version\\RFC6455\\Frame', $this->version->newFrame());
    }
}