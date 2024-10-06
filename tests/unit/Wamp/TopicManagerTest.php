<?php

namespace Ratchet\Wamp;

/**
 * @covers Ratchet\Wamp\TopicManager
 */
class TopicManagerTest extends \PHPUnit_Framework_TestCase {
    private $mock;

    private ?\Ratchet\Wamp\TopicManager $mngr = null;

    /**
     * @var \Ratchet\ConnectionInterface
     */
    private $conn;

    #[\Override]
    public function setUp() {
        $this->conn = $this->getMock(\Ratchet\ConnectionInterface::class);
        $this->mock = $this->getMock(\Ratchet\Wamp\WampServerInterface::class);
        $this->mngr = new TopicManager($this->mock);

        $this->conn->WAMP = new \StdClass;
        $this->mngr->onOpen($this->conn);
    }

    public function testGetTopicReturnsTopicObject(): void {
        $class = new \ReflectionClass(\Ratchet\Wamp\TopicManager::class);
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $topic = $method->invokeArgs($this->mngr, ['The Topic']);

        $this->assertInstanceOf(\Ratchet\Wamp\Topic::class, $topic);
    }

    public function testGetTopicCreatesTopicWithSameName(): void {
        $name = 'The Topic';

        $class = new \ReflectionClass(\Ratchet\Wamp\TopicManager::class);
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $topic = $method->invokeArgs($this->mngr, [$name]);

        $this->assertEquals($name, $topic->getId());
    }

    public function testGetTopicReturnsSameObject(): void {
        $class = new \ReflectionClass(\Ratchet\Wamp\TopicManager::class);
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $topic = $method->invokeArgs($this->mngr, ['No copy']);
        $again = $method->invokeArgs($this->mngr, ['No copy']);

        $this->assertSame($topic, $again);
    }

    public function testOnOpen(): void {
        $this->mock->expects($this->once())->method('onOpen');
        $this->mngr->onOpen($this->conn);
    }

    public function testOnCall(): void {
        $id = uniqid();

        $this->mock->expects($this->once())->method('onCall')->with(
            $this->conn,
            $id,
            $this->isInstanceOf(\Ratchet\Wamp\Topic::class),
            []
        );

        $this->mngr->onCall($this->conn, $id, 'new topic', []);
    }

    public function testOnSubscribeCreatesTopicObject(): void {
        $this->mock->expects($this->once())->method('onSubscribe')->with(
            $this->conn,
            $this->isInstanceOf(\Ratchet\Wamp\Topic::class)
        );

        $this->mngr->onSubscribe($this->conn, 'new topic');
    }

    public function testTopicIsInConnectionOnSubscribe(): void {
        $name = 'New Topic';

        $class = new \ReflectionClass(\Ratchet\Wamp\TopicManager::class);
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $topic = $method->invokeArgs($this->mngr, [$name]);

        $this->mngr->onSubscribe($this->conn, $name);

        $this->assertTrue($this->conn->WAMP->subscriptions->contains($topic));
    }

    public function testDoubleSubscriptionFiresOnce(): void {
        $this->mock->expects($this->exactly(1))->method('onSubscribe');

        $this->mngr->onSubscribe($this->conn, 'same topic');
        $this->mngr->onSubscribe($this->conn, 'same topic');
    }

    public function testUnsubscribeEvent(): void {
        $name = 'in and out';
        $this->mock->expects($this->once())->method('onUnsubscribe')->with(
            $this->conn,
            $this->isInstanceOf(\Ratchet\Wamp\Topic::class)
        );

        $this->mngr->onSubscribe($this->conn, $name);
        $this->mngr->onUnsubscribe($this->conn, $name);
    }

    public function testUnsubscribeFiresOnce(): void {
        $name = 'getting sleepy';
        $this->mock->expects($this->exactly(1))->method('onUnsubscribe');

        $this->mngr->onSubscribe($this->conn, $name);
        $this->mngr->onUnsubscribe($this->conn, $name);
        $this->mngr->onUnsubscribe($this->conn, $name);
    }

    public function testUnsubscribeRemovesTopicFromConnection(): void {
        $name = 'Bye Bye Topic';

        $class = new \ReflectionClass(\Ratchet\Wamp\TopicManager::class);
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $topic = $method->invokeArgs($this->mngr, [$name]);

        $this->mngr->onSubscribe($this->conn, $name);
        $this->mngr->onUnsubscribe($this->conn, $name);

        $this->assertFalse($this->conn->WAMP->subscriptions->contains($topic));
    }

    public function testOnPublishBubbles(): void {
        $msg = 'Cover all the code!';

        $this->mock->expects($this->once())->method('onPublish')->with(
            $this->conn,
            $this->isInstanceOf(\Ratchet\Wamp\Topic::class),
            $msg,
            $this->isType('array'),
            $this->isType('array')
        );

        $this->mngr->onPublish($this->conn, 'topic coverage', $msg, [], []);
    }

    public function testOnCloseBubbles(): void {
        $this->mock->expects($this->once())->method('onClose')->with($this->conn);
        $this->mngr->onClose($this->conn);
    }

    protected function topicProvider($name) {
        $class = new \ReflectionClass(\Ratchet\Wamp\TopicManager::class);
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $attribute = $class->getProperty('topicLookup');
        $attribute->setAccessible(true);

        $topic = $method->invokeArgs($this->mngr, [$name]);

        return [$topic, $attribute];
    }

    public function testConnIsRemovedFromTopicOnClose(): void {
        $name = 'State Testing';
        [$topic, $attribute] = $this->topicProvider($name);

        $this->assertCount(1, $attribute->getValue($this->mngr));

        $this->mngr->onSubscribe($this->conn, $name);
        $this->mngr->onClose($this->conn);

        $this->assertFalse($topic->has($this->conn));
    }

    public static function topicConnExpectationProvider() {
        return [
            ['onClose', 0], ['onUnsubscribe', 0],
        ];
    }

    /**
     * @dataProvider topicConnExpectationProvider
     */
    public function testTopicRetentionFromLeavingConnections($methodCall, $expectation): void {
        $topicName = 'checkTopic';
        [$topic, $attribute] = $this->topicProvider($topicName);

        $this->mngr->onSubscribe($this->conn, $topicName);
        call_user_func_array([$this->mngr, $methodCall], [$this->conn, $topicName]);

        $this->assertCount($expectation, $attribute->getValue($this->mngr));
    }

    public function testOnErrorBubbles(): void {
        $e = new \Exception('All work and no play makes Chris a dull boy');
        $this->mock->expects($this->once())->method('onError')->with($this->conn, $e);

        $this->mngr->onError($this->conn, $e);
    }

    public function testGetSubProtocolsReturnsArray(): void {
        $this->assertInternalType('array', $this->mngr->getSubProtocols());
    }

    public function testGetSubProtocolsBubbles(): void {
        $subs = ['hello', 'world'];
        $app = $this->getMock(\Ratchet\Wamp\Stub\WsWampServerInterface::class);
        $app->expects($this->once())->method('getSubProtocols')->will($this->returnValue($subs));
        $mngr = new TopicManager($app);

        $this->assertEquals($subs, $mngr->getSubProtocols());
    }
}
