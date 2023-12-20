<?php

namespace Ratchet\Wamp;

use PHPUnit\Framework\TestCase;
use Ratchet\Mock\Connection;
use Ratchet\Mock\WampComponent as TestComponent;

/**
 * @covers \Ratchet\Wamp\ServerProtocol
 * @covers \Ratchet\Wamp\WampServerInterface
 * @covers \Ratchet\Wamp\WampConnection
 */
class ServerProtocolTest extends TestCase
{
    protected $component;

    protected $app;

    public function setUp(): void
    {
        $this->app = new TestComponent;
        $this->component = new ServerProtocol($this->app);
    }

    protected function newConnection(): Connection
    {
        return new Connection;
    }

    public static function invalidMessageProvider(): array
    {
        return [
            [0], [3], [4], [8], [9],
        ];
    }

    /**
     * @dataProvider invalidMessageProvider
     */
    public function testInvalidMessages($type)
    {
        $this->expectException(\Ratchet\Wamp\Exception::class);

        $connection = $this->newConnection();
        $this->component->onOpen($connection);
        $this->component->onMessage($connection, json_encode([$type]));
    }

    public function testWelcomeMessage()
    {
        $connection = $this->newConnection();

        $this->component->onOpen($connection);

        $message = $connection->last['send'];
        $json = json_decode($message);

        $this->assertEquals(4, count($json));
        $this->assertEquals(0, $json[0]);
        $this->assertTrue(is_string($json[1]));
        $this->assertEquals(1, $json[2]);
    }

    public function testSubscribe()
    {
        $uri = 'http://example.com';
        $clientMessage = [5, $uri];

        $connection = $this->newConnection();

        $this->component->onOpen($connection);
        $this->component->onMessage($connection, json_encode($clientMessage));

        $this->assertEquals($uri, $this->app->last['onSubscribe'][1]);
    }

    public function testUnSubscribe()
    {
        $uri = 'http://example.com/endpoint';
        $clientMessage = [6, $uri];

        $connection = $this->newConnection();

        $this->component->onOpen($connection);
        $this->component->onMessage($connection, json_encode($clientMessage));

        $this->assertEquals($uri, $this->app->last['onUnSubscribe'][1]);
    }

    public static function callProvider(): array
    {
        return [
            [2, 'a', 'b'], [2, ['a', 'b']], [1, 'one'], [3, 'one', 'two', 'three'], [3, ['un', 'deux', 'trois']], [2, 'hi', ['hello', 'world']], [2, ['hello', 'world'], 'hi'], [2, ['hello' => 'world', 'herp' => 'derp']],
        ];
    }

    /**
     * @dataProvider callProvider
     */
    public function testCall(): void
    {
        $args = func_get_args();
        $paramNum = array_shift($args);

        $uri = 'http://example.com/endpoint/'.rand(1, 100);
        $id = uniqid('', false);
        $clientMessage = array_merge([2, $id, $uri], $args);

        $connection = $this->newConnection();

        $this->component->onOpen($connection);
        $this->component->onMessage($connection, json_encode($clientMessage));

        $this->assertEquals($id, $this->app->last['onCall'][1]);
        $this->assertEquals($uri, $this->app->last['onCall'][2]);

        $this->assertEquals($paramNum, count($this->app->last['onCall'][3]));
    }

    public function testPublish(): void
    {
        $connection = $this->newConnection();

        $topic = 'pubsubhubbub';
        $event = 'Here I am, publishing data';

        $clientMessage = [7, $topic, $event];

        $this->component->onOpen($connection);
        $this->component->onMessage($connection, json_encode($clientMessage));

        $this->assertEquals($topic, $this->app->last['onPublish'][1]);
        $this->assertEquals($event, $this->app->last['onPublish'][2]);
        $this->assertEquals([], $this->app->last['onPublish'][3]);
        $this->assertEquals([], $this->app->last['onPublish'][4]);
    }

    public function testPublishAndExcludeMe(): void
    {
        $connection = $this->newConnection();

        $this->component->onOpen($connection);
        $this->component->onMessage($connection, json_encode([7, 'topic', 'event', true]));

        $this->assertEquals($connection->WAMP->sessionId, $this->app->last['onPublish'][3][0]);
    }

    public function testPublishAndEligible(): void
    {
        $connection = $this->newConnection();

        $buddy = uniqid('', false);
        $friend = uniqid('', false);

        $this->component->onOpen($connection);
        $this->component->onMessage($connection, json_encode([7, 'topic', 'event', false, [$buddy, $friend]]));

        $this->assertEquals([], $this->app->last['onPublish'][3]);
        $this->assertEquals(2, count($this->app->last['onPublish'][4]));
    }

    public static function eventProvider(): array
    {
        return [
            ['http://example.com', ['one', 'two']], ['curie', [['hello' => 'world', 'herp' => 'derp']]],
        ];
    }

    /**
     * @dataProvider eventProvider
     */
    public function testEvent($topic, $payload): void
    {
        $connection = new WampConnection($this->newConnection());
        $connection->event($topic, $payload);

        $eventString = $connection->last['send'];

        $this->assertSame([8, $topic, $payload], json_decode($eventString, true));
    }

    public function testOnClosePropagation(): void
    {
        $connection = new Connection;

        $this->component->onOpen($connection);
        $this->component->onClose($connection);

        $class = new \ReflectionClass(WampConnection::class);
        $method = $class->getMethod('getConnection');
        $method->setAccessible(true);

        $check = $method->invokeArgs($this->app->last['onClose'][0], []);

        $this->assertSame($connection, $check);
    }

    public function testOnErrorPropagation(): void
    {
        $connection = new Connection;

        $exception = new \Exception('Nope');

        $this->component->onOpen($connection);
        $this->component->onError($connection, $exception);

        $class = new \ReflectionClass(WampConnection::class);
        $method = $class->getMethod('getConnection');
        $method->setAccessible(true);

        $check = $method->invokeArgs($this->app->last['onError'][0], []);

        $this->assertSame($connection, $check);
        $this->assertSame($exception, $this->app->last['onError'][1]);
    }

    public function testPrefix(): void
    {
        $connection = new WampConnection($this->newConnection());
        $this->component->onOpen($connection);

        $prefix = 'incoming';
        $fullURI = "http://example.com/$prefix";
        $method = 'call';

        $this->component->onMessage($connection, json_encode([1, $prefix, $fullURI]));

        $this->assertEquals($fullURI, $connection->WAMP->prefixes[$prefix]);
        $this->assertEquals("$fullURI#$method", $connection->getUri("$prefix:$method"));
    }

    public function testMessageMustBeJson(): void
    {
        $this->expectException(JsonException::class);

        $connection = new Connection;

        $this->component->onOpen($connection);
        $this->component->onMessage($connection, 'Hello World!');
    }

    public function testGetSubProtocolsReturnsArray(): void
    {
        $this->assertTrue(is_array($this->component->getSubProtocols()));
    }

    public function testGetSubProtocolsGetFromApp(): void
    {
        $this->app->protocols = ['hello', 'world'];

        $this->assertGreaterThanOrEqual(3, count($this->component->getSubProtocols()));
    }

    public function testWampOnMessageApp(): void
    {
        $app = $this->createMock(WampServerInterface::class);
        $wamp = new ServerProtocol($app);

        $this->assertContains('wamp', $wamp->getSubProtocols());
    }

    public static function badFormatProvider(): array
    {
        return [
            [json_encode(true)], ['{"valid":"json", "invalid": "message"}'], ['{"0": "fail", "hello": "world"}'],
        ];
    }

    /**
     * @dataProvider badFormatProvider
     */
    public function testValidJsonButInvalidProtocol($message)
    {
        $this->expectException(\Ratchet\Wamp\Exception::class);

        $connection = $this->newConnection();
        $this->component->onOpen($connection);
        $this->component->onMessage($connection, $message);
    }

    public function testBadClientInputFromNonStringTopic()
    {
        $this->expectException(\Ratchet\Wamp\Exception::class);

        $connection = new WampConnection($this->newConnection());
        $this->component->onOpen($connection);

        $this->component->onMessage($connection, json_encode([5, ['hells', 'nope']]));
    }

    public function testBadPrefixWithNonStringTopic()
    {
        $this->expectException(\Ratchet\Wamp\Exception::class);

        $connection = new WampConnection($this->newConnection());
        $this->component->onOpen($connection);

        $this->component->onMessage($connection, json_encode([1, ['hells', 'nope'], ['bad', 'input']]));
    }

    public function testBadPublishWithNonStringTopic()
    {
        $this->expectException(\Ratchet\Wamp\Exception::class);

        $connection = new WampConnection($this->newConnection());
        $this->component->onOpen($connection);

        $this->component->onMessage($connection, json_encode([7, ['bad', 'input'], 'Hider']));
    }
}
