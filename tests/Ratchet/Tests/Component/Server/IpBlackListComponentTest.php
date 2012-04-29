<?php
namespace Ratchet\Tests\Component\Server;
use Ratchet\Component\Server\IpBlackListComponent;
use Ratchet\Tests\Mock\Connection;
use Ratchet\Tests\Mock\Component as MockComponent;

/**
 * @covers Ratchet\Component\Server\IpBlackListComponent
 */
class IpBlackListComponentTest extends \PHPUnit_Framework_TestCase {
    protected $_comp;
    protected $_mock;

    public function setUp() {
        $this->_mock = new MockComponent;
        $this->_comp = new IpBlackListComponent($this->_mock);
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

        $this->_comp
            ->blockAddress($unblock)
            ->blockAddress($blockOne)
            ->unblockAddress($unblock)
            ->blockAddress($blockTwo)
        ;

        $this->assertEquals(array($blockOne, $blockTwo), $this->_comp->getBlockedAddresses());
    }

    public function testDecoratingMethods() {
        $conn1 = new Connection;
        $conn2 = new Connection;
        $conn3 = new Connection;

        $this->_comp->onOpen($conn1);
        $this->_comp->onOpen($conn3);
        $this->_comp->onOpen($conn2);
        $this->assertSame($conn2, $this->_mock->last['onOpen'][0]);

        $msg = 'Hello World!';
        $this->_comp->onMessage($conn1, $msg);
        $this->assertSame($conn1, $this->_mock->last['onMessage'][0]);
        $this->assertEquals($msg, $this->_mock->last['onMessage'][1]);

        $this->_comp->onClose($conn3);
        $this->assertSame($conn3, $this->_mock->last['onClose'][0]);

        try {
            throw new \Exception('I threw an error');
        } catch (\Exception $e) {
        }

        $this->_comp->onError($conn2, $e);
        $this->assertEquals($conn2, $this->_mock->last['onError'][0]);
        $this->assertEquals($e, $this->_mock->last['onError'][1]);
    }

    public function addressProvider() {
        return array(
            array('127.0.0.1', '127.0.0.1')
          , array('localhost', 'localhost')
          , array('fe80::1%lo0', 'fe80::1%lo0')
          , array('127.0.0.1', '127.0.0.1:6392')
        );
    }

    /**
     * @dataProvider addressProvider
     */
    public function testFilterAddress($expected, $input) {
        $this->assertEquals($expected, $this->_comp->filterAddress($input));
    }

    public function testUnblockingSilentlyFails() {
        $this->assertInstanceOf('\\Ratchet\\Component\\Server\\IpBlackListComponent', $this->_comp->unblockAddress('localhost'));
    }
}