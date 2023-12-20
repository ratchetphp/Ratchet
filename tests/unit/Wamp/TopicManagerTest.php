<?php

namespace Ratchet\Wamp;

use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Stub\WsWampServerInterface;

/**
 * @covers Ratchet\Wamp\TopicManager
 */
class TopicManagerTest extends TestCase
{
    private $mock;

    private TopicManager $manager;

    private ConnectionInterface $connection;

    public function setUp(): void
    {
        $this->connection = $this->createMock(ConnectionInterface::class);
        $this->mock = $this->createMock(WampServerInterface::class);
        $this->manager = new TopicManager($this->mock);

        $this->connection->WAMP = new \StdClass;
        $this->manager->onOpen($this->connection);
    }

    public function testGetTopicReturnsTopicObject()
    {
        $class = new \ReflectionClass(TopicManager::class);
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $topic = $method->invokeArgs($this->manager, ['The Topic']);

        $this->assertInstanceOf(Topic::class, $topic);
    }

    public function testGetTopicCreatesTopicWithSameName()
    {
        $name = 'The Topic';

        $class = new \ReflectionClass(TopicManager::class);
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $topic = $method->invokeArgs($this->manager, [$name]);

        $this->assertEquals($name, $topic->getId());
    }

    public function testGetTopicReturnsSameObject()
    {
        $class = new \ReflectionClass(TopicManager::class);
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $topic = $method->invokeArgs($this->manager, ['No copy']);
        $again = $method->invokeArgs($this->manager, ['No copy']);

        $this->assertSame($topic, $again);
    }

    public function testOnOpen(): void
    {
        $this->mock->expects($this->once())->method('onOpen');
        $this->manager->onOpen($this->connection);
    }

    public function testOnCall(): void
    {
        $id = uniqid();

        $this->mock->expects($this->once())->method('onCall')->with(
            $this->connection,
            $id,
            $this->isInstanceOf(Topic::class),
            []
        );

        $this->manager->onCall($this->connection, $id, 'new topic', []);
    }

    public function testOnSubscribeCreatesTopicObject(): void
    {
        $this->mock->expects($this->once())->method('onSubscribe')->with(
            $this->connection,
            $this->isInstanceOf(Topic::class)
        );

        $this->manager->onSubscribe($this->connection, 'new topic');
    }

    public function testTopicIsInConnectionOnSubscribe(): void
    {
        $name = 'New Topic';

        $class = new \ReflectionClass(TopicManager::class);
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $topic = $method->invokeArgs($this->manager, [$name]);

        $this->manager->onSubscribe($this->connection, $name);

        $this->assertTrue($this->connection->WAMP->subscriptions->contains($topic));
    }

    public function testDoubleSubscriptionFiresOnce(): void
    {
        $this->mock->expects($this->exactly(1))->method('onSubscribe');

        $this->manager->onSubscribe($this->connection, 'same topic');
        $this->manager->onSubscribe($this->connection, 'same topic');
    }

    public function testUnsubscribeEvent(): void
    {
        $name = 'in and out';
        $this->mock->expects($this->once())->method('onUnsubscribe')->with(
            $this->connection,
            $this->isInstanceOf(Topic::class)
        );

        $this->manager->onSubscribe($this->connection, $name);
        $this->manager->onUnsubscribe($this->connection, $name);
    }

    public function testUnsubscribeFiresOnce(): void
    {
        $name = 'getting sleepy';
        $this->mock->expects($this->exactly(1))->method('onUnsubscribe');

        $this->manager->onSubscribe($this->connection, $name);
        $this->manager->onUnsubscribe($this->connection, $name);
        $this->manager->onUnsubscribe($this->connection, $name);
    }

    public function testUnsubscribeRemovesTopicFromConnection(): void
    {
        $name = 'Bye Bye Topic';

        $class = new \ReflectionClass(TopicManager::class);
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $topic = $method->invokeArgs($this->manager, [$name]);

        $this->manager->onSubscribe($this->connection, $name);
        $this->manager->onUnsubscribe($this->connection, $name);

        $this->assertFalse($this->connection->WAMP->subscriptions->contains($topic));
    }

    public function testOnPublishBubbles()
    {
        $message = 'Cover all the code!';

        $this->mock->expects($this->once())->method('onPublish')->with(
            $this->connection,
            $this->isInstanceOf(Topic::class),
            $message,
            $this->isType('array'),
            $this->isType('array')
        );

        $this->manager->onPublish($this->connection, 'topic coverage', $message, [], []);
    }

    public function testOnCloseBubbles()
    {
        $this->mock->expects($this->once())->method('onClose')->with($this->connection);
        $this->manager->onClose($this->connection);
    }

    protected function topicProvider($name)
    {
        $class = new \ReflectionClass(TopicManager::class);
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $attribute = $class->getProperty('topicLookup');
        $attribute->setAccessible(true);

        $topic = $method->invokeArgs($this->manager, [$name]);

        return [$topic, $attribute];
    }

    public function testConnIsRemovedFromTopicOnClose()
    {
        $name = 'State Testing';
        [$topic, $attribute] = $this->topicProvider($name);

        $this->assertCount(1, $attribute->getValue($this->manager));

        $this->manager->onSubscribe($this->connection, $name);
        $this->manager->onClose($this->connection);

        $this->assertFalse($topic->has($this->connection));
    }

    public static function topicConnExpectationProvider(): array
    {
        return [
            ['onClose', 0],
            ['onUnsubscribe', 0],
        ];
    }

    /**
     * @dataProvider topicConnExpectationProvider
     */
    public function testTopicRetentionFromLeavingConnections($methodCall, $expectation)
    {
        $topicName = 'checkTopic';
        [$topic, $attribute] = $this->topicProvider($topicName);

        $this->manager->onSubscribe($this->connection, $topicName);
        call_user_func_array([$this->manager, $methodCall], [$this->connection, $topicName]);

        $this->assertCount($expectation, $attribute->getValue($this->manager));
    }

    public function testOnErrorBubbles()
    {
        $exception = new \Exception('All work and no play makes Chris a dull boy');
        $this->mock->expects($this->once())->method('onError')->with($this->connection, $exception);

        $this->manager->onError($this->connection, $exception);
    }

    public function testGetSubProtocolsReturnsArray()
    {
        $this->assertIsArray($this->manager->getSubProtocols());
    }

    public function testGetSubProtocolsBubbles()
    {
        $subs = ['hello', 'world'];
        $app = $this->createMock(WsWampServerInterface::class);
        $app->expects($this->once())->method('getSubProtocols')->willReturn($subs);
        $manager = new TopicManager($app);

        $this->assertEquals($subs, $manager->getSubProtocols());
    }
}
