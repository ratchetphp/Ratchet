<?php
namespace Ratchet\WebSocket\Version;
use Ratchet\WebSocket\Version\Hixie76;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

/**
 * @covers Ratchet\WebSocket\Version\Hixie76
 */
class Hixie76Test extends \PHPUnit_Framework_TestCase {
    protected $_crlf = "\r\n";
    protected $_body = '6dW+XgKfWV0=';

    protected $_version;

    public function setUp() {
        $this->_version = new Hixie76;
    }

    public function testClassImplementsVersionInterface() {
        $constraint = $this->isInstanceOf('\\Ratchet\\WebSocket\\Version\\VersionInterface');
        $this->assertThat($this->_version, $constraint);
    }

    /**
     * @dataProvider keyProvider
     */
    public function testKeySigningForHandshake($accept, $key) {
        $this->assertEquals($accept, $this->_version->generateKeyNumber($key));
    }

    public static function keyProvider() {
        return array(
            array(179922739, '17  9 G`ZD9   2 2b 7X 3 /r90')
          , array(906585445, '3e6b263  4 17 80')
          , array(0, '3e6b26341780')
        );
    }

    public function headerProvider() {
        $key1 = base64_decode('QTN+ICszNiA2IDJvICBWOG4gNyAgc08yODhZ');
        $key2 = base64_decode('TzEyICAgeVsgIFFSNDUgM1IgLiAyOFggNC00dn4z');

        $headers  = "GET / HTTP/1.1";
        $headers .= "Upgrade: WebSocket{$this->_crlf}";
        $headers .= "Connection: Upgrade{$this->_crlf}";
        $headers .= "Host: socketo.me{$this->_crlf}";
        $headers .= "Origin: http://fiddle.jshell.net{$this->_crlf}";
        $headers .= "Sec-WebSocket-Key1:17 Z4< F94 N3  7P41  7{$this->_crlf}";
        $headers .= "Sec-WebSocket-Key2:1 23C3:,2% 1-29  4 f0{$this->_crlf}";
        $headers .= "(Key3):70:00:EE:6E:33:20:90:69{$this->_crlf}";
        $headers .= $this->_crlf;

        return $headers;
    }

    public function testNoUpgradeBeforeBody() {
        $headers = $this->headerProvider();

        $mockConn = $this->getMock('\Ratchet\ConnectionInterface');
        $mockApp  = $this->getMock('\Ratchet\MessageComponentInterface');

        $server = new HttpServer(new WsServer($mockApp));
        $server->onOpen($mockConn);
        $mockApp->expects($this->exactly(0))->method('onOpen');
        $server->onMessage($mockConn, $headers);
    }

    public function testTcpFragmentedUpgrade() {
        $headers = $this->headerProvider();
        $body    = base64_decode($this->_body);

        $mockConn = $this->getMock('\Ratchet\ConnectionInterface');
        $mockApp  = $this->getMock('\Ratchet\MessageComponentInterface');

        $server = new HttpServer(new WsServer($mockApp));
        $server->onOpen($mockConn);
        $server->onMessage($mockConn, $headers);

        $mockApp->expects($this->once())->method('onOpen');
        $server->onMessage($mockConn, $body . $this->_crlf . $this->_crlf);
    }
}