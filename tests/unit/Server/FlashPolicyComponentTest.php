<?php
namespace Ratchet\Application\Server;
use Ratchet\Server\FlashPolicy;

/**
 * @covers Ratchet\Server\FlashPolicy
 */
class FlashPolicyTest extends \PHPUnit_Framework_TestCase {

    protected $_policy;

    public function setUp() {
        $this->_policy = new FlashPolicy();
    }

    public function testPolicyRender() {
        $this->_policy->setSiteControl('all');
        $this->_policy->addAllowedAccess('example.com', '*');
        $this->_policy->addAllowedAccess('dev.example.com', '*');

        $this->assertInstanceOf('SimpleXMLElement', $this->_policy->renderPolicy());
    }

    public function testInvalidPolicyReader() {
        $this->setExpectedException('UnexpectedValueException');
        $this->_policy->renderPolicy();
    }

    public function testInvalidDomainPolicyReader() {
        $this->setExpectedException('UnexpectedValueException');
        $this->_policy->setSiteControl('all');
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
          , array(false, 'by-content-type')
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
          , array(true, '*.example.com')
          , array(true, 'www.example.com')
          , array(true, 'dev.dev.example.com')
          , array(true, 'http://example.com')
          , array(true, 'https://example.com')
          , array(true, 'http://*.example.com')
          , array(false, 'exam*ple.com')
          , array(true, '127.0.255.1')
          , array(true, 'localhost')
          , array(false, 'www.example.*')
          , array(false, 'www.exa*le.com')
          , array(false, 'www.example.*com')
          , array(false, '*.example.*')
          , array(false, 'gasldf*$#a0sdf0a8sdf')
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
          , array(true, '507,516-523,333')
          , array(true, '507,516-523,507,516-523')
          , array(false, '516-')
          , array(true, '516-523,11')
          , array(false, '516,-523,11')
          , array(false, 'example')
          , array(false, 'asdf,123')
          , array(false, '--')
          , array(false, ',,,')
          , array(false, '838*')
        );
    }

    public function testAddAllowedAccessOnlyAcceptsValidPorts() {
        $this->setExpectedException('UnexpectedValueException');

        $this->_policy->addAllowedAccess('*', 'nope');
    }

    public function testSetSiteControlThrowsException() {
        $this->setExpectedException('UnexpectedValueException');

        $this->_policy->setSiteControl('nope');
    }

    public function testErrorClosesConnection() {
        $conn = $this->getMock('\\Ratchet\\ConnectionInterface');
        $conn->expects($this->once())->method('close');

        $this->_policy->onError($conn, new \Exception);
    }

    public function testOnMessageSendsString() {
        $this->_policy->addAllowedAccess('*', '*');

        $conn = $this->getMock('\\Ratchet\\ConnectionInterface');
        $conn->expects($this->once())->method('send')->with($this->isType('string'));

        $this->_policy->onMessage($conn, ' ');
    }

    public function testOnOpenExists() {
        $this->assertTrue(method_exists($this->_policy, 'onOpen'));
        $conn = $this->getMock('\Ratchet\ConnectionInterface');
        $this->_policy->onOpen($conn);
    }

    public function testOnCloseExists() {
        $this->assertTrue(method_exists($this->_policy, 'onClose'));
        $conn = $this->getMock('\Ratchet\ConnectionInterface');
        $this->_policy->onClose($conn);
    }
}
