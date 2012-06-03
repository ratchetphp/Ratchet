<?php
namespace Ratchet\Tests\WebSocket\Version\RFC6455;
use Ratchet\WebSocket\Version\RFC6455\Frame;

/**
 * @covers Ratchet\WebSocket\Version\RFC6455\Frame
 * @todo getMaskingKey, getPayloadStartingByte don't have tests yet
 * @todo Could use some clean up in general, I had to rush to fix a bug for a deadline, sorry.
 */
class FrameTest extends \PHPUnit_Framework_TestCase {
    protected $_firstByteFinText    = '10000001';
    protected $_secondByteMaskedSPL = '11111101';

    protected $_frame;

    protected $_packer;

    public function setUp() {
        $this->_frame = new Frame;
    }

    /**
     * This is a data provider
     * @param string The UTF8 message
     * @param string The WebSocket framed message, then base64_encoded
     */
    public static function UnframeMessageProvider() {
        return array(
            array('Hello World!',                'gYydAIfa1WXrtvIg0LXvbOP7')
          , array('!@#$%^&*()-=_+[]{}\|/.,<>`~', 'gZv+h96r38f9j9vZ+IHWrvOWoayF9oX6gtfRqfKXwOeg')
          , array('ಠ_ಠ',                         'gYfnSpu5B/g75gf4Ow==')
          , array("The quick brown fox jumps over the lazy dog.  All work and no play makes Chris a dull boy.  I'm trying to get past 128 characters for a unit test here...", 'gf4Amahb14P8M7Kj2S6+4MN7tfHHLLmjzjSvo8IuuvPbe7j1zSn398A+9+/JIa6jzDSwrYh7lu/Ee6Ds2jD34sY/9+3He6fvySL37skwsvCIGL/xwSj34og/ou/Ee7Xs0XX3o+F8uqPcKa7qxjz398d7sObce6fi2y/3sppj9+DAOqXiyy+y8dt7sezae7aj3TW+94gvsvDce7/m2j75rYY=')
        );
    }

    public static function underflowProvider() {
        return array(
            array('isFinal', '')
          , array('getOpcode', '')
          , array('isMasked', '10000001')
          , array('getPayloadLength', '10000001')
          , array('getPayloadLength', '1000000111111110')
          , array('getMaskingKey', '1000000110000111')
          , array('getPayload', '100000011000000100011100101010101001100111110100')
        );
    }

    /**
     * @dataProvider underflowProvider
     */
    public function testUnderflowExceptionFromAllTheMethodsMimickingBuffering($method, $bin) {
        $this->setExpectedException('\UnderflowException');

        if (!empty($bin)) {
            $this->_frame->addBuffer(Frame::encode($bin));
        }

        call_user_func(array($this->_frame, $method));
    }

    /**
     * A data provider for testing the first byte of a WebSocket frame
     * @param bool Given, is the byte indicate this is the final frame
     * @param int Given, what is the expected opcode
     * @param string of 0|1 Each character represents a bit in the byte
     */
    public static function firstByteProvider() {
        return array(
            array(false, 8,  '00001000')
          , array(true,  10, '10001010')
          , array(false, 15, '00001111')
          , array(true,   1, '10000001')
          , array(true,  15, '11111111')
        );
    }

    /**
     * @dataProvider firstByteProvider
     */
    public function testFinCodeFromBits($fin, $opcode, $bin) {
        $this->_frame->addBuffer(Frame::encode($bin));
        $this->assertEquals($fin, $this->_frame->isFinal());
    }

    /**
     * @dataProvider UnframeMessageProvider
     */
    public function testFinCodeFromFullMessage($msg, $encoded) {
        $this->_frame->addBuffer(base64_decode($encoded));
        $this->assertTrue($this->_frame->isFinal());
    }

    /**
     * @dataProvider firstByteProvider
     */
    public function testOpcodeFromBits($fin, $opcode, $bin) {
        $this->_frame->addBuffer(Frame::encode($bin));
        $this->assertEquals($opcode, $this->_frame->getOpcode());
    }

    /**
     * @dataProvider UnframeMessageProvider
     */
    public function testOpcodeFromFullMessage($msg, $encoded) {
        $this->_frame->addBuffer(base64_decode($encoded));
        $this->assertEquals(1, $this->_frame->getOpcode());
    }

    public static function payloadLengthDescriptionProvider() {
        return array(
            array(7,  '01110101')
          , array(7,  '01111101')
          , array(23, '01111110')
          , array(71, '01111111')
          , array(7,  '00000000') // Should this throw an exception?  Can a payload be empty?
          , array(7,  '00000001')
        );
    }

    /**
     * @dataProvider payloadLengthDescriptionProvider
     */
    public function testFirstPayloadDesignationValue($bits, $bin) {
        $this->_frame->addBuffer(Frame::encode($this->_firstByteFinText));
        $this->_frame->addBuffer(Frame::encode($bin));

        $ref = new \ReflectionClass($this->_frame);
        $cb  = $ref->getMethod('getFirstPayloadVal');
        $cb->setAccessible(true);

        $this->assertEquals(bindec($bin), $cb->invoke($this->_frame));
    }

    /**
     * @dataProvider payloadLengthDescriptionProvider
     */
    public function testDetermineHowManyBitsAreUsedToDescribePayload($expected_bits, $bin) {
        $this->_frame->addBuffer(Frame::encode($this->_firstByteFinText));
        $this->_frame->addBuffer(Frame::encode($bin));

        $ref = new \ReflectionClass($this->_frame);
        $cb  = $ref->getMethod('getNumPayloadBits');
        $cb->setAccessible(true);

        $this->assertEquals($expected_bits, $cb->invoke($this->_frame));
    }

    public function secondByteProvider() {
        return array(
            array(true,   1, '10000001')
          , array(false,  1, '00000001')
          , array(true, 125, $this->_secondByteMaskedSPL)
        );
    }

    /**
     * @dataProvider secondByteProvider
     */
    public function testIsMaskedReturnsExpectedValue($masked, $payload_length, $bin) {
        $this->_frame->addBuffer(Frame::encode($this->_firstByteFinText));
        $this->_frame->addBuffer(Frame::encode($bin));

        $this->assertEquals($masked, $this->_frame->isMasked());
    }

    /**
     * @dataProvider UnframeMessageProvider
     */
    public function testIsMaskedFromFullMessage($msg, $encoded) {
        $this->_frame->addBuffer(base64_decode($encoded));
        $this->assertTrue($this->_frame->isMasked());
    }

    /**
     * @dataProvider secondByteProvider
     */
    public function testGetPayloadLengthWhenOnlyFirstFrameIsUsed($masked, $payload_length, $bin) {
        $this->_frame->addBuffer(Frame::encode($this->_firstByteFinText));
        $this->_frame->addBuffer(Frame::encode($bin));

        $this->assertEquals($payload_length, $this->_frame->getPayloadLength());
    }

    /**
     * @dataProvider UnframeMessageProvider
     * @todo Not yet testing when second additional payload length descriptor
     */
    public function testGetPayloadLengthFromFullMessage($msg, $encoded) {
        $this->_frame->addBuffer(base64_decode($encoded));
        $this->assertEquals(strlen($msg), $this->_frame->getPayloadLength());
    }

    /**
     * @todo Use a masking key generator when one is coded later
     */
    protected function generateMask() {
        $mask = '';
        for($i = 0; $i < 4; $i++) {
            $mask .= chr(rand(0, 255));
        }

        return $mask;
    }

    public function maskingKeyProvider() {
        return array(
            array($this->generateMask())
          , array($this->generateMask())
          , array($this->generateMask())
        );
    }

    /**
     * @dataProvider maskingKeyProvider
     * @todo I I wrote the dataProvider incorrectly, skpping for now
     */
    public function testGetMaskingKey($mask) {
        $this->_frame->addBuffer(Frame::encode($this->_firstByteFinText));
        $this->_frame->addBuffer(Frame::encode($this->_secondByteMaskedSPL));
        $this->_frame->addBuffer($mask);

        $this->assertEquals($mask, $this->_frame->getMaskingKey());
    }

    /**
     * @dataProvider UnframeMessageProvider
     * @todo Move this test to bottom as it requires all methods of the class
     */
    public function testUnframeFullMessage($unframed, $base_framed) {
        $this->_frame->addBuffer(base64_decode($base_framed));
        $this->assertEquals($unframed, $this->_frame->getPayload());
    }

    public static function messageFragmentProvider() {
        return array(
            array(false, '', '', '', '', '')
        );
    }

    /**
     * @dataProvider UnframeMessageProvider
     */
    public function testCheckPiecingTogetherMessage($msg, $encoded) {
        $framed = base64_decode($encoded);
        for ($i = 0, $len = strlen($framed);$i < $len; $i++) {
            $this->_frame->addBuffer(substr($framed, $i, 1));
        }

        $this->assertEquals($msg, $this->_frame->getPayload());
    }

    public function testCreate() {
        $len = 65525;
        $len = 65575;
        $pl  = $this->generateRandomString($len);

        $frame = Frame::create($pl, true, Frame::OP_PING);

        $this->assertTrue($frame->isFinal());
        $this->assertEquals(Frame::OP_PING, $frame->getOpcode());
        $this->assertFalse($frame->isMasked());
        $this->assertEquals($len, $frame->getPayloadLength());
        $this->assertEquals($pl, $frame->getPayload());
    }

    public function testExtractOverflow() {
        $string1 = $this->generateRandomString();
        $frame1  = Frame::create($string1);

        $string2 = $this->generateRandomString();
        $frame2  = Frame::create($string2);

        $cat = new Frame;
        $cat->addBuffer($frame1->data . $frame2->data);

        $this->assertEquals($string1, $cat->getPayload());

        $uncat = new Frame;
        $uncat->addBuffer($cat->extractOverflow());

        $this->assertEquals($string1, $cat->getPayload());
        $this->assertEquals($string2, $uncat->getPayload());
    }

    public function testEmptyExtractOverflow() {
        $string = $this->generateRandomString();
        $frame  = Frame::create($string);

        $this->assertEquals($string, $frame->getPayload());
        $this->assertEquals('', $frame->extractOverflow());
        $this->assertEquals($string, $frame->getPayload());
    }

    protected function generateRandomString($length = 10, $addSpaces = true, $addNumbers = true) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"$%&/()=[]{}'; // ยง

        $useChars = array();
        for($i = 0; $i < $length; $i++) {
            $useChars[] = $characters[mt_rand(0, strlen($characters) - 1)];
        }

        if($addSpaces === true) {
            array_push($useChars, ' ', ' ', ' ', ' ', ' ', ' ');
        }

        if($addNumbers === true) {
            array_push($useChars, rand(0, 9), rand(0, 9), rand(0, 9));
        }

        shuffle($useChars);

        $randomString = trim(implode('', $useChars));
        $randomString = substr($randomString, 0, $length);

        return $randomString;
    }
}