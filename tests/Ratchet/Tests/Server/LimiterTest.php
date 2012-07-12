<?php
namespace Ratchet\Tests\Server;
use Ratchet\Server\Limiter;
use Ratchet\Tests\Mock\Component as MockComponent;
use Ratchet\Tests\Mock\Connection;

/**
 * @covers Ratchet\Server\Limiter
 */
class LimiterTest extends \PHPUnit_Framework_TestCase {
    protected $app;

    protected $limit;

    public function setUp() {
        $this->app   = new MockComponent;
        $this->limit = new Limiter($this->app);
    }

    public function testMaxConnections() {
        $this->limit->maxConnections(3);

        $conn1 = new Connection;
        $conn2 = new Connection;
        $conn3 = new Connection;

        $this->limit->onOpen($conn1);
        $this->limit->onOpen($conn2);
        $this->limit->onOpen($conn3);

        $nope = new Connection;
        $this->limit->onOpen($nope);

        $this->assertFalse($conn3->last['close']);
        $this->assertTrue($nope->last['close']);
    }

    public function testDecoratingMethods() {
        $conns = array();
        for ($i = 1; $i <= 3; $i++) {
            $conns[$i] = new Connection;
        }

        $this->app   = new MockComponent;
        $this->limit = new Limiter($this->app);

        $this->limit->onOpen($conns[1]);
        $this->limit->onOpen($conns[3]);
        $this->limit->onOpen($conns[2]);
        $this->assertSame($conns[2], $this->app->last['onOpen'][0]);

        $msg = 'Hello World!';
        $this->limit->onMessage($conns[1], $msg);
        $this->assertSame($conns[1], $this->app->last['onMessage'][0]);
        $this->assertEquals($msg, $this->app->last['onMessage'][1]);

        $this->limit->onClose($conns[3]);
        $this->assertSame($conns[3], $this->app->last['onClose'][0]);

        $e = new \Exception('I threw an error');

        $this->limit->onError($conns[2], $e);
        $this->assertEquals($conns[2], $this->app->last['onError'][0]);
        $this->assertEquals($e, $this->app->last['onError'][1]);
    }

    public function testGetSubProtocolsReturnsArray() {
        $this->assertTrue(is_array($this->limit->getSubProtocols()));
    }

    public function testGetSubProtocolsGetFromApp() {
        $this->app->protocols = array('hello', 'world');

        $this->assertGreaterThanOrEqual(2, count($this->limit->getSubProtocols()));
    }
}