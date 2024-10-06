<?php

namespace Ratchet\Wamp;

/**
 * @covers Ratchet\Wamp\Topic
 */
class TopicTest extends \PHPUnit_Framework_TestCase {
    public function testGetId(): void {
        $id = uniqid();
        $topic = new Topic($id);

        $this->assertEquals($id, $topic->getId());
    }

    public function testAddAndCount(): void {
        $topic = new Topic('merp');

        $topic->add($this->newConn());
        $topic->add($this->newConn());
        $topic->add($this->newConn());

        $this->assertEquals(3, count($topic));
    }

    public function testRemove(): void {
        $topic = new Topic('boop');
        $tracked = $this->newConn();

        $topic->add($this->newConn());
        $topic->add($tracked);
        $topic->add($this->newConn());

        $topic->remove($tracked);

        $this->assertEquals(2, count($topic));
    }

    public function testBroadcast(): void {
        $msg = 'Hello World!';
        $name = 'Batman';
        $protocol = json_encode([8, $name, $msg]);

        $first = $this->getMock(\Ratchet\Wamp\WampConnection::class, ['send'], [$this->getMock(\Ratchet\ConnectionInterface::class)]);
        $second = $this->getMock(\Ratchet\Wamp\WampConnection::class, ['send'], [$this->getMock(\Ratchet\ConnectionInterface::class)]);

        $first->expects($this->once())
            ->method('send')
            ->with($this->equalTo($protocol));

        $second->expects($this->once())
            ->method('send')
            ->with($this->equalTo($protocol));

        $topic = new Topic($name);
        $topic->add($first);
        $topic->add($second);

        $topic->broadcast($msg);
    }

    public function testBroadcastWithExclude(): void {
        $msg = 'Hello odd numbers';
        $name = 'Excluding';
        $protocol = json_encode([8, $name, $msg]);

        $first = $this->getMock(\Ratchet\Wamp\WampConnection::class, ['send'], [$this->getMock(\Ratchet\ConnectionInterface::class)]);
        $second = $this->getMock(\Ratchet\Wamp\WampConnection::class, ['send'], [$this->getMock(\Ratchet\ConnectionInterface::class)]);
        $third = $this->getMock(\Ratchet\Wamp\WampConnection::class, ['send'], [$this->getMock(\Ratchet\ConnectionInterface::class)]);

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

        $topic->broadcast($msg, [$second->WAMP->sessionId]);
    }

    public function testBroadcastWithEligible(): void {
        $msg = 'Hello white list';
        $name = 'Eligible';
        $protocol = json_encode([8, $name, $msg]);

        $first = $this->getMock(\Ratchet\Wamp\WampConnection::class, ['send'], [$this->getMock(\Ratchet\ConnectionInterface::class)]);
        $second = $this->getMock(\Ratchet\Wamp\WampConnection::class, ['send'], [$this->getMock(\Ratchet\ConnectionInterface::class)]);
        $third = $this->getMock(\Ratchet\Wamp\WampConnection::class, ['send'], [$this->getMock(\Ratchet\ConnectionInterface::class)]);

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

        $topic->broadcast($msg, [], [$first->WAMP->sessionId, $third->WAMP->sessionId]);
    }

    public function testIterator(): void {
        $first = $this->newConn();
        $second = $this->newConn();
        $third = $this->newConn();

        $topic = new Topic('Joker');
        $topic->add($first)->add($second)->add($third);

        $check = [$first, $second, $third];

        foreach ($topic as $mock) {
            $this->assertNotSame(false, array_search($mock, $check));
        }
    }

    public function testToString(): void {
        $name = 'Bane';
        $topic = new Topic($name);

        $this->assertEquals($name, (string) $topic);
    }

    public function testDoesHave(): void {
        $conn = $this->newConn();
        $topic = new Topic('Two Face');
        $topic->add($conn);

        $this->assertTrue($topic->has($conn));
    }

    public function testDoesNotHave(): void {
        $conn = $this->newConn();
        $topic = new Topic('Alfred');

        $this->assertFalse($topic->has($conn));
    }

    public function testDoesNotHaveAfterRemove(): void {
        $conn = $this->newConn();
        $topic = new Topic('Ras');

        $topic->add($conn)->remove($conn);

        $this->assertFalse($topic->has($conn));
    }

    protected function newConn() {
        return new WampConnection($this->getMock(\Ratchet\ConnectionInterface::class));
    }
}
