<?php

namespace Ratchet\Wamp;

use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;

/**
 * @covers Ratchet\Wamp\WampConnection
 */
class WampConnectionTest extends TestCase
{
    protected $connection;

    protected $mock;

    public function setUp(): void
    {
        $this->mock = $this->getMockBuilder(ConnectionInterface::class)->getMock();
        $this->connection = new WampConnection($this->mock);
    }

    public function testCallResult(): void
    {
        $callId = uniqid();
        $data = ['hello' => 'world', 'herp' => 'derp'];

        $this->mock->expects($this->once())->method('send')->with(json_encode([3, $callId, $data]));

        $this->connection->callResult($callId, $data);
    }

    public function testCallError(): void
    {
        $callId = uniqid();
        $uri = 'http://example.com/end/point';

        $this->mock->expects($this->once())->method('send')->with(json_encode([4, $callId, $uri, '']));

        $this->connection->callError($callId, $uri);
    }

    public function testCallErrorWithTopic(): void
    {
        $callId = uniqid();
        $uri = 'http://example.com/end/point';

        $this->mock->expects($this->once())->method('send')->with(json_encode([4, $callId, $uri, '']));

        $this->connection->callError($callId, new Topic($uri));
    }

    public function testDetailedCallError(): void
    {
        $callId = uniqid();
        $uri = 'http://example.com/end/point';
        $desc = 'beep boop beep';
        $detail = 'Error: Too much awesome';

        $this->mock->expects($this->once())->method('send')->with(json_encode([4, $callId, $uri, $desc, $detail]));

        $this->connection->callError($callId, $uri, $desc, $detail);
    }

    public function testPrefix(): void
    {
        $shortOut = 'outgoing';
        $longOut = 'http://example.com/outgoing';

        $this->mock->expects($this->once())->method('send')->with(json_encode([1, $shortOut, $longOut]));

        $this->connection->prefix($shortOut, $longOut);
    }

    public function testGetUriWhenNoCurieGiven(): void
    {
        $uri = 'http://example.com/noshort';

        $this->assertEquals($uri, $this->connection->getUri($uri));
    }

    public function testClose(): void
    {
        $mock = $this->getMockBuilder(ConnectionInterface::class)->getMock();
        $connection = new WampConnection($mock);

        $mock->expects($this->once())->method('close');

        $connection->close();
    }
}
