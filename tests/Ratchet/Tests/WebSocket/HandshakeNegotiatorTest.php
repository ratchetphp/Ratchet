<?php
namespace Ratchet\Tests\WebSocket;
use Ratchet\WebSocket\HandshakeNegotiator;
use Ratchet\WebSocket\WsConnection;
use Ratchet\Tests\Mock\Connection as ConnectionStub;
use Ratchet\WebSocket\Version\RFC6455;
use Ratchet\WebSocket\Version\HyBi10;
use Ratchet\WebSocket\Version\Hixie76;
use Guzzle\Http\Message\EntityEnclosingRequest;

/**
 * @covers Ratchet\WebSocket\HandshakeNegotiator
 */
class HandshakeNegotiatorTest extends \PHPUnit_Framework_TestCase {
    protected $parser;

    public function setUp() {
        $this->parser = new HandshakeNegotiator();
    }

    public function headersProvider() {
        return array(
            array(false, "GET / HTTP/1.1\r\nHost: socketo.me\r\n")
          , array(true,  "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n")
          , array(false, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n1")
          , array(false, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie✖")
          , array(true,  "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie✖\r\n\r\n")
          , array(false, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie\r\n")
        );
    }

    /**
     * @dataProvider headersProvider
     */
    public function testIsEom($expected, $message) {
        $this->assertEquals($expected, $this->parser->isEom($message));
    }

    public function testFluentInterface() {
        $rfc = new RFC6455;

        $this->assertSame($this->parser, $this->parser->disableVersion(13));
        $this->assertSame($this->parser, $this->parser->enableVersion($rfc));
    }

    public function testGetVersion() {
        $this->parser->disableVersion(13);
        $rfc = new RFC6455;
        $this->parser->enableVersion($rfc);

        $req = new EntityEnclosingRequest('get', '/', array(
            'Host' => 'socketo.me'
          , 'Sec-WebSocket-Version' => 13
        ));

        $this->assertSame($rfc, $this->parser->getVersion($req));
    }

    public function testGetNopeVersionAndDisable() {
        $this->parser->disableVersion(13);

        $req = new EntityEnclosingRequest('get', '/', array(
            'Host' => 'socketo.me'
          , 'Sec-WebSocket-Version' => 13
        ));

        $this->assertNull($this->parser->getVersion($req));
    }

    public function testGetSupportedVersionString() {
        $v1 = new RFC6455;
        $v2 = new HyBi10;

        $parser = new HandshakeNegotiator();
        $parser->enableVersion($v1);
        $parser->enableVersion($v2);

        $string = $parser->getSupportedVersionString();
        $values = explode(',', $string);

        $this->assertContains($v1->getVersionNumber(), $values);
        $this->assertContains($v2->getVersionNumber(), $values);
    }

    public function testGetSupportedVersionAfterRemoval() {
        $this->parser->disableVersion(0);

        $values = explode(',', $this->parser->getSupportedVersionString());

        $this->assertEquals(2, count($values));
        $this->assertFalse(array_search(0, $values));
    }

    public function testBufferOverflowResponse() {
        $conn = new WsConnection(new ConnectionStub);
        $this->parser->onOpen($conn);

        $this->parser->maxSize = 20;

        $this->assertNull($this->parser->onData($conn, "GET / HTTP/1.1\r\n"));
        $this->assertGreaterThan(400, $this->parser->onData($conn, "Header-Is: Too Big")->getStatusCode());
    }
}