<?php
namespace Ratchet\Tests\Protocol\WebSocket\Version;
use Ratchet\Protocol\WebSocket\Version\Hybi10;

/**
 * @covers Ratchet\Protocol\WebSocket\Version\Hybi10
 */
class Hybi10Test extends \PHPUnit_Framework_TestCase {
    protected $_version;

    public function setUp() {
        $this->_version = new Hybi10();
    }

    public function testClassImplementsVersionInterface() {
        $constraint = $this->isInstanceOf('\\Ratchet\\Protocol\\WebSocket\\Version\\VersionInterface');
        $this->assertThat($this->_version, $constraint);
    }

    /**
     * @dataProvider HandshakeProvider
     */
    public function testKeySigningForHandshake($key, $accept) {
        $this->assertEquals($accept, $this->_version->sign($key));
    }

    public static function HandshakeProvider() {
        return Array(
            Array('x3JJHMbDL1EzLkh9GBhXDw==', 'HSmrc0sMlYUkAGmm5OPpG2HaGWk=')
          , Array('dGhlIHNhbXBsZSBub25jZQ==', 's3pPLMBiTxaQ9kYGzzhZRbK+xOo=')
        );
    }
}