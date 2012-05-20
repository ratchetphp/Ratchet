<?php
namespace Ratchet\Tests\WebSocket\Version;
use Ratchet\WebSocket\Version\Hixie76;

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
//          , array(906585445, '3e6b263  4 17 80')
          , array('', '3e6b263 4 17 80')
          , array('', '3e6b63 4 17 80')
          , array('', '3e6b6341780')
        );
    }
}