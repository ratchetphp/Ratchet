<?php
namespace Ratchet\WebSocket\Version;
use Ratchet\WebSocket\Version\HyBi10;
use Ratchet\WebSocket\Version\RFC6455\Frame;

/**
 * @covers Ratchet\WebSocket\Version\Hybi10
 */
class HyBi10Test extends \PHPUnit_Framework_TestCase {
    protected $_version;

    public function setUp() {
        $this->_version = new HyBi10();
    }

    /**
     * Is this useful?
     */
    public function testClassImplementsVersionInterface() {
        $constraint = $this->isInstanceOf('\\Ratchet\\WebSocket\\Version\\VersionInterface');
        $this->assertThat($this->_version, $constraint);
    }

    /**
     * @dataProvider HandshakeProvider
     */
    public function testKeySigningForHandshake($key, $accept) {
        $this->assertEquals($accept, $this->_version->sign($key));
    }

    public static function HandshakeProvider() {
        return array(
            array('x3JJHMbDL1EzLkh9GBhXDw==', 'HSmrc0sMlYUkAGmm5OPpG2HaGWk=')
          , array('dGhlIHNhbXBsZSBub25jZQ==', 's3pPLMBiTxaQ9kYGzzhZRbK+xOo=')
        );
    }

    /**
     * @dataProvider UnframeMessageProvider
     */
    public function testUnframeMessage($message, $framed) {
//        $decoded = $this->_version->unframe(base64_decode($framed));
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
        $string   = 'Hello World!';
        $framed   = $this->_version->newFrame($string)->getContents();

        $frame = new Frame;
        $frame->addBuffer($framed);

        $this->assertEquals($string, $frame->getPayload());
    }
}