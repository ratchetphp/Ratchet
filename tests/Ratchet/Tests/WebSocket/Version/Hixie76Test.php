<?php
namespace Ratchet\Tests\WebSocket\Version;
use Ratchet\WebSocket\Version\Hixie76;
use Ratchet\WebSocket\WsServer;

/**
 * @covers Ratchet\WebSocket\Version\Hixie76
 */
class Hixie76Test extends \PHPUnit_Framework_TestCase {
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
          , array('', '17  9 G`ZD9   2 2b 7X 3 /r91')
          , array(906585445, '3e6b263  4 17 80')
          , array('', '3e6b263 4 17 80')
          , array('', '3e6b63 4 17 80')
          , array('', '3e6b6341780')
        );
    }

    public function testTcpFragmentedUpgrade() {
        $key1 = base64_decode('QTN+ICszNiA2IDJvICBWOG4gNyAgc08yODhZ');
        $key2 = base64_decode('TzEyICAgeVsgIFFSNDUgM1IgLiAyOFggNC00dn4z');
        $body = base64_decode('6dW+XgKfWV0=');

        $crlf = "\r\n";

        $headers  = "GET / HTTP/1.1";
        $headers .= "Upgrade: WebSocket{$crlf}";
        $headers .= "Connection: Upgrade{$crlf}";
        $headers .= "Host: home.chrisboden.ca{$crlf}";
        $headers .= "Origin: http://fiddle.jshell.net{$crlf}";
        $headers .= "Sec-WebSocket-Key1:17 Z4< F94 N3  7P41  7{$crlf}";
        $headers .= "Sec-WebSocket-Key2:1 23C3:,2% 1-29  4 f0{$crlf}";
        $headers .= "(Key3):70:00:EE:6E:33:20:90:69{$crlf}";
        $headers .= $crlf;

        $mockConn = $this->getMock('\\Ratchet\\ConnectionInterface');
        $mockApp = $this->getMock('\\Ratchet\\MessageComponentInterface');

        $server = new WsServer($mockApp);
        $server->onOpen($mockConn);
        $server->onMessage($mockConn, $headers);

        $mockApp->expects($this->once())->method('onOpen');
        $server->onMessage($mockConn, $body . $crlf . $crlf);
    }
}