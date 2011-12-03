<?php
namespace Ratchet\Tests\Application\WebSocket\Version\RFC6455;
use Ratchet\Application\WebSocket\Version\RFC6455\HandshakeVerifier;

/**
 * @covers Ratchet\Application\WebSocket\Version\RFC6455\HandshakeVerifier
 */
class HandshakeVerifierTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Ratchet\Application\WebSocket\Version\RFC6455\HandshakeVerifier
     */
    protected $_v;

    public function setUp() {
        $this->_v = new HandshakeVerifier;
    }

    public static function methodProvider() {
        return array(
            array(true,  'GET')
          , array(false, 'get') // I'm not sure if this is valid or not, need to check standard
          , array(false, 'POST')
          , array(false, 'DELETE')
          , array(false, 'PUT')
          , array(false, 'PATCH')
        );
    }

    /**
     * @dataProvider methodProvider
     */
    public function testMethodMustBeGet($result, $in) {
        $this->assertEquals($result, $this->_v->verifyMethod($in));
    }

    public static function httpVersionProvider() {
        return array(
            array(true,  1.1)
          , array(true,  '1.1')
          , array(true,  1.2)
          , array(true,  '1.2')
          , array(true,  2)
          , array(true,  '2')
          , array(true,  '2.0')
          , array(false, '1.0')
          , array(false, 1)
          , array(false, '0.9')
          , array(false, '')
          , array(false, 'hello')
        );
    }

    /**
     * @dataProvider httpVersionProvider
     */
    public function testHttpVersionIsAtLeast1Point1($expected, $in) {
        $this->assertEquals($expected, $this->_v->verifyHTTPVersion($in));
    }

    /**
     * @todo Add failing values in here
     */
    public static function uRIProvider() {
        return array(
            array(true, '/chat')
        );
    }

    /**
     * @dataProvider URIProvider
     */
    public function testRequestUri($expected, $in) {
        return $this->markTestIncomplete('Method this test is testing is incomplete');

        $this->assertEquals($expected, $this->_v->verifyRequestURI($in));
    }

    public static function hostProvider() {
        return array(
            array(true, 'server.example.com')
          , array(false, null)
        );
    }

    /**
     * @dataProvider HostProvider
     */
    public function testVerifyHostIsSet($expected, $in) {
        $this->assertEquals($expected, $this->_v->verifyHost($in));
    }

    public static function upgradeProvider() {
        return array(
            array(true,  'websocket')
          , array(false, 'Websocket')
          , array(false, null)
          , array(false, '')
        );
    }

    /**
     * @dataProvider upgradeProvider
     */
    public function testVerifyUpgradeIsWebSocket($expected, $val) {
        $this->assertEquals($expected, $this->_v->verifyUpgradeRequest($val));
    }

    public static function connectionProvider() {
        return array(
            array(true,  'Upgrade')
          , array(false, 'upgrade')
          , array(true,  'keep-alive, Upgrade')
          , array(true,  'Upgrade, keep-alive')
          , array(true,  'keep-alive, Upgrade, something')
          , array(false, '')
          , array(false, null)
        );
    }

    /**
     * @dataProvider connectionProvider
     */
    public function testConnectionHeaderVerification($expected, $val) {
        $this->assertEquals($expected, $this->_v->verifyConnection($val));
    }

    public static function keyProvider() {
        return array(
            array(true,  'hkfa1L7uwN6DCo4IS3iWAw==')
          , array(true,  '765vVoQpKSGJwPzJIMM2GA==')
          , array(true,  'AQIDBAUGBwgJCgsMDQ4PEC==')
          , array(true,  'axa2B/Yz2CdpfQAY2Q5P7w==')
          , array(false, 0)
          , array(false, 'Hello World')
          , array(false, '1234567890123456')
          , array(false, '123456789012345678901234')
        );
    }

    /**
     * @dataProvider keyProvider
     */
    public function testKeyIsBase64Encoded16BitNonce($expected, $val) {
        $this->assertEquals($expected, $this->_v->verifyKey($val));
    }

    public static function versionProvider() {
        return array(
            array(true,  13)
          , array(true,  '13')
          , array(false, 12)
          , array(false, 14)
          , array(false, '14')
          , array(false, 'hi')
          , array(false, '')
          , array(false, null)
        );
    }

    /**
     * @dataProvider versionProvider
     */
    public function testVersionEquals13($expected, $in) {
        $this->assertEquals($expected, $this->_v->verifyVersion($in));
    }
}