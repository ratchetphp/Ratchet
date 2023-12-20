<?php

namespace Ratchet\Wamp;

use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;

/**
 * @covers Ratchet\Wamp\Topic
 */
class TopicTest extends TestCase
{
    public function testGetId(): void
    {
        $id = uniqid();
        $topic = new Topic($id);

        $this->assertEquals($id, $topic->getId());
    }

    public function testAddAndCount(): void
    {
        $topic = new Topic('merp');

        $topic->add($this->newConnection());
        $topic->add($this->newConnection());
        $topic->add($this->newConnection());

        $this->assertEquals(3, count($topic));
    }

    public function testRemove(): void
    {
        $topic = new Topic('boop');
        $tracked = $this->newConnection();

        $topic->add($this->newConnection());
        $topic->add($tracked);
        $topic->add($this->newConnection());

        $topic->remove($tracked);

        $this->assertEquals(2, count($topic));
    }

    public function testBroadcast(): void
    {
        $message = 'Hello World!';
        $name = 'Batman';
        $protocol = json_encode([8, $name, $message]);

        $mockBuilder = function () {
            return $this->getMockBuilder(WampConnection::class)
                ->setMethods(['send'])
                ->setConstructorArgs([$this->getMockBuilder(ConnectionInterface::class)->getMock()])
                ->getMock();
        };
        $first = $mockBuilder();
        $second = $mockBuilder();

        $first->expects($this->once())
            ->method('send')
            ->with($this->equalTo($protocol));

        $second->expects($this->once())
            ->method('send')
            ->with($this->equalTo($protocol));

        $topic = new Topic($name);
        $topic->add($first);
        $topic->add($second);

        $topic->broadcast($message);
    }

    public function testBroadcastWithExclude(): void
    {
        $message = 'Hello odd numbers';
        $name = 'Excluding';
        $protocol = json_encode([8, $name, $message]);

        $mockBuilder = function () {
            return $this->getMockBuilder(WampConnection::class)
                ->setMethods(['send'])
                ->setConstructorArgs([$this->getMockBuilder(ConnectionInterface::class)->getMock()])
                ->getMock();
        };
        $first = $mockBuilder();
        $second = $mockBuilder();
        $third = $mockBuilder();

        $first->expects($this->once())
            ->method('send')
            ->with($this->equalTo($protocol));

        $second->expects($this->never())->method('send');

        $third->expects($this->once())
            ->method('send')
            ->with($this->equalTo($protocol));

        $topic = new Topic($name);
        $topic->add($first);
        $topic->add($second);
        $topic->add($third);

        $topic->broadcast($message, [$second->WAMP->sessionId]);
    }

    public function testBroadcastWithEligible(): void
    {
        $message = 'Hello white list';
        $name = 'Eligible';
        $protocol = json_encode([8, $name, $message]);

        $mockBuilder = function () {
            return $this->getMockBuilder(WampConnection::class)
                ->setMethods(['send'])
                ->setConstructorArgs([$this->getMockBuilder(ConnectionInterface::class)->getMock()])
                ->getMock();
        };
        $first = $mockBuilder();
        $second = $mockBuilder();
        $third = $mockBuilder();

        $first->expects($this->once())
            ->method('send')
            ->with($this->equalTo($protocol));

        $second->expects($this->never())->method('send');

        $third->expects($this->once())
            ->method('send')
            ->with($this->equalTo($protocol));

        $topic = new Topic($name);
        $topic->add($first);
        $topic->add($second);
        $topic->add($third);

        $topic->broadcast($message, [], [$first->WAMP->sessionId, $third->WAMP->sessionId]);
    }

    public function testIterator(): void
    {
        $first = $this->newConnection();
        $second = $this->newConnection();
        $third = $this->newConnection();

        $topic = new Topic('Joker');
        $topic->add($first)->add($second)->add($third);

        $check = [$first, $second, $third];

        foreach ($topic as $mock) {
            $this->assertNotSame(false, array_search($mock, $check));
        }
    }

    public function testToString(): void
    {
        $name = 'Bane';
        $topic = new Topic($name);

        $this->assertEquals($name, (string) $topic);
    }

    public function testDoesHave(): void
    {
        $connection = $this->newConnection();
        $topic = new Topic('Two Face');
        $topic->add($connection);

        $this->assertTrue($topic->has($connection));
    }

    public function testDoesNotHave(): void
    {
        $connection = $this->newConnection();
        $topic = new Topic('Alfred');

        $this->assertFalse($topic->has($connection));
    }

    public function testDoesNotHaveAfterRemove(): void
    {
        $connection = $this->newConnection();
        $topic = new Topic('Ras');

        $topic->add($connection)->remove($connection);

        $this->assertFalse($topic->has($connection));
    }

    protected function newConnection(): WampConnection
    {
        return new WampConnection($this->getMockBuilder(ConnectionInterface::class)->getMock());
    }
}
