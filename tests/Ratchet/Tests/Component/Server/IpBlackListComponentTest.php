<?php
namespace Ratchet\Tests\Component\Server;
use Ratchet\Component\Server\IpBlackListComponent;
use Ratchet\Tests\Mock\Connection;
use Ratchet\Tests\Mock\Component as MockComponent;

/**
 * @covers Ratchet\Component\Server\IpBlackList
 */
class IpBlackListComponentTest extends \PHPUnit_Framework_TestCase {
    protected $_comp;

    public function setUp() {
        $this->_comp = new IpBlackListComponent(new MockComponent);
    }

    public function testBlockAndCloseOnOpen() {
        $conn = new Connection;

        $this->_comp->blockAddress($conn->remoteAddress);

        $ret  = $this->_comp->onOpen($conn);

        $this->assertInstanceOf('\\Ratchet\\Resource\\Command\\Action\\CloseConnection', $ret);
    }

    public function testAddAndRemoveWithFluentInterfaces() {
        $blockOne = '127.0.0.1';
        $blockTwo = '192.168.1.1';
        $unblock  = '75.119.207.140';

        $this->_comp->blockAddress($unblock)->blockAddress($blockOne)->unblockAddress($unblock)->blockAddress($blockTwo);

        $this->assertEquals(array($blockOne, $blockTwo), $this->_comp->getBlockedAddresses());
    }
}