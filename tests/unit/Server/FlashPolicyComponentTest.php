<?php

namespace Ratchet\Application\Server;

use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;
use Ratchet\Server\FlashPolicy;
use SimpleXMLElement;

/**
 * @covers Ratchet\Server\FlashPolicy
 */
class FlashPolicyTest extends TestCase
{
    protected FlashPolicy $policy;

    public function setUp(): void
    {
        $this->policy = new FlashPolicy;
    }

    public function testPolicyRender(): void
    {
        $this->policy->setSiteControl('all');
        $this->policy->addAllowedAccess('example.com', '*');
        $this->policy->addAllowedAccess('dev.example.com', '*');

        $this->assertInstanceOf(SimpleXMLElement::class, $this->policy->renderPolicy());
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testInvalidPolicyReader()
    {
        $this->policy->renderPolicy();
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testInvalidDomainPolicyReader()
    {
        $this->policy->setSiteControl('all');
        $this->policy->addAllowedAccess('dev.example.*', '*');
        $this->policy->renderPolicy();
    }

    /**
     * @dataProvider siteControl
     */
    public function testSiteControlValidation($accept, $permittedCrossDomainPolicies): void
    {
        $this->assertEquals($accept, $this->policy->validateSiteControl($permittedCrossDomainPolicies));
    }

    public static function siteControl(): array
    {
        return [
            [true, 'all'], [true, 'none'], [true, 'master-only'], [false, 'by-content-type'], [false, 'by-ftp-filename'], [false, ''], [false, 'all '], [false, 'asdf'], [false, '@893830'], [false, '*'],
        ];
    }

    /**
     * @dataProvider URI
     */
    public function testDomainValidation($accept, $domain): void
    {
        $this->assertEquals($accept, $this->policy->validateDomain($domain));
    }

    public static function URI(): array
    {
        return [
            [true, '*'], [true, 'example.com'], [true, 'exam-ple.com'], [true, '*.example.com'], [true, 'www.example.com'], [true, 'dev.dev.example.com'], [true, 'http://example.com'], [true, 'https://example.com'], [true, 'http://*.example.com'], [false, 'exam*ple.com'], [true, '127.0.255.1'], [true, 'localhost'], [false, 'www.example.*'], [false, 'www.exa*le.com'], [false, 'www.example.*com'], [false, '*.example.*'], [false, 'gasldf*$#a0sdf0a8sdf'],
        ];
    }

    /**
     * @dataProvider ports
     */
    public function testPortValidation($accept, $ports)
    {
        $this->assertEquals($accept, $this->policy->validatePorts($ports));
    }

    public static function ports()
    {
        return [
            [true, '*'], [true, '80'], [true, '80,443'], [true, '507,516-523'], [true, '507,516-523,333'], [true, '507,516-523,507,516-523'], [false, '516-'], [true, '516-523,11'], [false, '516,-523,11'], [false, 'example'], [false, 'asdf,123'], [false, '--'], [false, ',,,'], [false, '838*'],
        ];
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testAddAllowedAccessOnlyAcceptsValidPorts()
    {
        $this->policy->addAllowedAccess('*', 'nope');
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testSetSiteControlThrowsException()
    {
        $this->policy->setSiteControl('nope');
    }

    public function testErrorClosesConnection(): void
    {
        $connection = $this->getMockBuilder(ConnectionInterface::class)->getMock();
        $connection->expects($this->once())->method('close');

        $this->policy->onError($connection, new \Exception);
    }

    public function testOnMessageSendsString(): void
    {
        $this->policy->addAllowedAccess('*', '*');

        $connection = $this->getMockBuilder(ConnectionInterface::class)->getMock();
        $connection->expects($this->once())->method('send')->with($this->isType('string'));

        $this->policy->onMessage($connection, ' ');
    }

    public function testOnOpenExists(): void
    {
        $this->assertTrue(method_exists($this->policy, 'onOpen'));
        $connection = $this->getMockBuilder(ConnectionInterface::class)->getMock();
        $this->policy->onOpen($connection);
    }

    public function testOnCloseExists(): void
    {
        $this->assertTrue(method_exists($this->policy, 'onClose'));
        $connection = $this->getMockBuilder(ConnectionInterface::class)->getMock();
        $this->policy->onClose($connection);
    }
}
