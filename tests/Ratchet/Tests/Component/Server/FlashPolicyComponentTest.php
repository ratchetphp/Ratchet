<?php
namespace Ratchet\Tests\Application\Server;
use Ratchet\Component\Server\FlashPolicyComponent;

/**
 * @covers Ratchet\Component\WebSocket\Version\Hixie76
 */
class FlashPolicyComponentTest extends \PHPUnit_Framework_TestCase {

    protected $_policy;

    public function setUp() {
        $this->_policy = new FlashPolicyComponent();
    }


    public function testPolicyRender() {
        $this->_policy->setSiteControl('all');
        $this->_policy->addAllowedAccess('example.com', '*');
        $this->_policy->addAllowedAccess('dev.example.com', '*');
        $this->_policy->addAllowedHTTPRequestHeaders('*', '*');
        $this->assertInstanceOf('SimpleXMLElement', $this->_policy->renderPolicy());
    }

    public function testInvalidPolicyReader() {
        $this->setExpectedException('UnexpectedValueException');
        $this->_policy->addAllowedHTTPRequestHeaders('*', '*');
        $this->_policy->renderPolicy();
    }
    
    public function testAnotherInvalidPolicyReader() {
        $this->setExpectedException('UnexpectedValueException');
        $this->_policy->addAllowedHTTPRequestHeaders('*', '*');
        $this->_policy->addAllowedAccess('dev.example.com', '*');
        $this->_policy->renderPolicy();
    }

    public function testInvalidDomainPolicyReader() {
        $this->setExpectedException('UnexpectedValueException');
        $this->_policy->setSiteControl('all');
        $this->_policy->addAllowedHTTPRequestHeaders('*', '*');
        $this->_policy->addAllowedAccess('dev.example.*', '*');
        $this->_policy->renderPolicy();
    }
    
    
    /**
     * @dataProvider siteControl
     */
    public function testSiteControlValidation($accept, $permittedCrossDomainPolicies) {
        $this->assertEquals($accept, $this->_policy->validateSiteControl($permittedCrossDomainPolicies));
    }

    public static function siteControl() {
        return array(
            array(true, 'all')
          , array(true, 'none')
          , array(true, 'master-only')
          , array(true, 'by-content-type')
          , array(false, 'by-ftp-filename')
          , array(false, '')
          , array(false, 'all ')
          , array(false, 'asdf')
          , array(false, '@893830')
          , array(false, '*')
        );
    }


    /**
     * @dataProvider URI
     */
    public function testDomainValidation($accept, $domain) {
        $this->assertEquals($accept, $this->_policy->validateDomain($domain));
    }

    public static function URI() {
        return array(
            array(true, '*')
          , array(true, 'example.com')
          , array(true, 'exam-ple.com')
          , array(true, 'www.example.com')
          , array(true, 'http://example.com')
          , array(true, 'http://*.example.com')
          , array(false, 'exam*ple.com')
          , array(true, '127.0.0.1')
          , array(true, 'localhost')
          , array(false, 'www.example.*')
          , array(false, 'www.exa*le.com')
          , array(false, 'www.example.*com')
          , array(false, '*.example.*')
          , array(false, 'gasldf*$#a0sdf0a8sdf')
          , array(false, 'http://example.*')
        );
    }


    /**
     * @dataProvider ports
     */
    public function testPortValidation($accept, $ports) {
        $this->assertEquals($accept, $this->_policy->validatePorts($ports));
    }

    public static function ports() {
        return array(
            array(true, '*')
          , array(true, '80')
          , array(true, '80,443')
          , array(true, '507,516-523')
          , array(false, '233-11')
          , array(true, '507,516-523,333')
          , array(true, '507,516-523,507,516-523')
          , array(true, '516-523')
          , array(true, '516-523,11')
          , array(false, 'example')
          , array(false, 'asdf,123')
          , array(false, '--')
          , array(false, ',,,')
          , array(false, '838*')
        );
    }

    /**
     * @dataProvider headers
     */
    public function testHeaderValidation($accept, $headers) {
        $this->assertEquals($accept, $this->_policy->validateHeaders($headers));
    }

    public static function headers() {
        return array(
            array(true, '*')
          , array(true, 'X-Foo')
          , array(true, 'X-Foo*,hello')
          , array(false, 'X-Fo*o,hello')
          , array(false, '*ooo,hello')
          , array(false, 'X Foo')
          , array(false, false)
          , array(true, 'X-001')
          , array(false, '--')
          , array(false, '-')
        );
    }

    /**
     * @dataProvider bools
     */
    public function testSecureValidation($accept, $bool) {
        $this->assertEquals($accept, $this->_policy->validateSecure($bool));
    }

    public static function bools() {
        return array(
            array(true, true)
          , array(true, false)
          , array(false, 1)
          , array(false, 0)
          , array(false, 'false')
          , array(false, 'on')
          , array(false, 'yes')
          , array(false, '--')
          , array(false, '!')
        );
    }
}