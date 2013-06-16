<?php
namespace Ratchet\Server;
use Ratchet\Server\IpBlackList;

/**
 * @covers Ratchet\Server\IpBlackList
 */
class IpBlackListTest extends \PHPUnit_Framework_TestCase {
    protected $blocker;
    protected $mock;

    public function setUp() {
        $this->mock = $this->getMock('\\Ratchet\\MessageComponentInterface');
        $this->blocker = new IpBlackList($this->mock);
    }

    public function testOnOpen() {
        $this->mock->expects($this->exactly(3))->method('onOpen');

        $conn1 = $this->newConn();
        $conn2 = $this->newConn();
        $conn3 = $this->newConn();

        $this->blocker->onOpen($conn1);
        $this->blocker->onOpen($conn3);
        $this->blocker->onOpen($conn2);
    }

    public function testBlockDoesNotTriggerOnOpen() {
        $conn = $this->newConn();

        $this->blocker->blockAddress($conn->remoteAddress);

        $this->mock->expects($this->never())->method('onOpen');

        $ret = $this->blocker->onOpen($conn);
    }

    public function testBlockDoesNotTriggerOnClose() {
        $conn = $this->newConn();

        $this->blocker->blockAddress($conn->remoteAddress);

        $this->mock->expects($this->never())->method('onClose');

        $ret = $this->blocker->onOpen($conn);
    }

    public function testOnMessageDecoration() {
        $conn = $this->newConn();
        $msg  = 'Hello not being blocked';

        $this->mock->expects($this->once())->method('onMessage')->with($conn, $msg);

        $this->blocker->onMessage($conn, $msg);
    }

    public function testOnCloseDecoration() {
        $conn = $this->newConn();

        $this->mock->expects($this->once())->method('onClose')->with($conn);

        $this->blocker->onClose($conn);
    }

    public function testBlockClosesConnection() {
        $conn = $this->newConn();
        $this->blocker->blockAddress($conn->remoteAddress);

        $conn->expects($this->once())->method('close');

        $this->blocker->onOpen($conn);
    }

    public function testAddAndRemoveWithFluentInterfaces() {
        $blockOne = '127.0.0.1';
        $blockTwo = '192.168.1.1';
        $unblock  = '75.119.207.140';

        $this->blocker
            ->blockAddress($unblock)
            ->blockAddress($blockOne)
            ->unblockAddress($unblock)
            ->blockAddress($blockTwo)
        ;

        $this->assertEquals(array($blockOne, $blockTwo), $this->blocker->getBlockedAddresses());
    }

    public function testDecoratorPassesErrors() {
        $conn = $this->newConn();
        $e    = new \Exception('I threw an error');

        $this->mock->expects($this->once())->method('onError')->with($conn, $e);

        $this->blocker->onError($conn, $e);
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
        $this->assertEquals($expected, $this->blocker->filterAddress($input));
    }

    public function testUnblockingSilentlyFails() {
        $this->assertInstanceOf('\\Ratchet\\Server\\IpBlackList', $this->blocker->unblockAddress('localhost'));
    }

    protected function newConn() {
        $conn = $this->getMock('\\Ratchet\\ConnectionInterface');
        $conn->remoteAddress = '127.0.0.1';

        return $conn;
    }
}