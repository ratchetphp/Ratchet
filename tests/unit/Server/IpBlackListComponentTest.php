<?php

namespace Ratchet\Server;

use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * @covers Ratchet\Server\IpBlackList
 */
class IpBlackListTest extends TestCase
{
    protected IpBlackList $blocker;

    protected MessageComponentInterface $mock;

    public function setUp(): void
    {
        $this->mock = $this->getMockBuilder(MessageComponentInterface::class)->getMock();
        $this->blocker = new IpBlackList($this->mock);
    }

    public function testOnOpen()
    {
        $this->mock->expects($this->exactly(3))->method('onOpen');

        $connection1 = $this->newConnection();
        $connection2 = $this->newConnection();
        $connection3 = $this->newConnection();

        $this->blocker->onOpen($connection1);
        $this->blocker->onOpen($connection3);
        $this->blocker->onOpen($connection2);
    }

    public function testBlockDoesNotTriggerOnOpen()
    {
        $connection = $this->newConnection();

        $this->blocker->blockAddress($connection->remoteAddress);

        $this->mock->expects($this->never())->method('onOpen');

        $this->blocker->onOpen($connection);
    }

    public function testBlockDoesNotTriggerOnClose()
    {
        $connection = $this->newConnection();

        $this->blocker->blockAddress($connection->remoteAddress);

        $this->mock->expects($this->never())->method('onClose');

        $this->blocker->onOpen($connection);
    }

    public function testOnMessageDecoration()
    {
        $connection = $this->newConnection();
        $message = 'Hello not being blocked';

        $this->mock->expects($this->once())->method('onMessage')->with($connection, $message);

        $this->blocker->onMessage($connection, $message);
    }

    public function testOnCloseDecoration()
    {
        $connection = $this->newConnection();

        $this->mock->expects($this->once())->method('onClose')->with($connection);

        $this->blocker->onClose($connection);
    }

    public function testBlockClosesConnection()
    {
        $connection = $this->newConnection();
        $this->blocker->blockAddress($connection->remoteAddress);

        $connection->expects($this->once())->method('close');

        $this->blocker->onOpen($connection);
    }

    public function testAddAndRemoveWithFluentInterfaces()
    {
        $blockOne = '127.0.0.1';
        $blockTwo = '192.168.1.1';
        $unblock = '75.119.207.140';

        $this->blocker
            ->blockAddress($unblock)
            ->blockAddress($blockOne)
            ->unblockAddress($unblock)
            ->blockAddress($blockTwo);

        $this->assertEquals([$blockOne, $blockTwo], $this->blocker->getBlockedAddresses());
    }

    public function testDecoratorPassesErrors()
    {
        $connection = $this->newConnection();
        $exception = new \Exception('I threw an error');

        $this->mock->expects($this->once())->method('onError')->with($connection, $exception);

        $this->blocker->onError($connection, $exception);
    }

    public function addressProvider(): array
    {
        return [
            ['127.0.0.1', '127.0.0.1'], ['localhost', 'localhost'], ['fe80::1%lo0', 'fe80::1%lo0'], ['127.0.0.1', '127.0.0.1:6392'],
        ];
    }

    /**
     * @dataProvider addressProvider
     */
    public function testFilterAddress($expected, $input)
    {
        $this->assertEquals($expected, $this->blocker->filterAddress($input));
    }

    public function testUnblockingSilentlyFails()
    {
        $this->assertInstanceOf(IpBlackList::class, $this->blocker->unblockAddress('localhost'));
    }

    protected function newConnection(): ConnectionInterface
    {
        $connection = $this->getMockBuilder(ConnectionInterface::class)->getMock();
        $connection->remoteAddress = '127.0.0.1';

        return $connection;
    }
}
