<?php
namespace Ratchet\Wamp;

/**
 * @covers Ratchet\Wamp\WampConnection
 */
class WampConnectionTest extends \PHPUnit_Framework_TestCase {
    protected $conn;
    protected $mock;

    public function setUp() {
        $this->mock = $this->getMock('\\Ratchet\\ConnectionInterface');
        $this->conn = new WampConnection($this->mock);
    }

    public function testCallResult() {
        $callId = uniqid();
        $data   = array('hello' => 'world', 'herp' => 'derp');

        $this->mock->expects($this->once())->method('send')->with(json_encode(array(3, $callId, $data)));

        $this->conn->callResult($callId, $data);
    }

    public function testCallError() {
        $callId = uniqid();
        $uri    = 'http://example.com/end/point';

        $this->mock->expects($this->once())->method('send')->with(json_encode(array(4, $callId, $uri, '')));

        $this->conn->callError($callId, $uri);
    }

    public function testCallErrorWithTopic() {
        $callId = uniqid();
        $uri    = 'http://example.com/end/point';

        $this->mock->expects($this->once())->method('send')->with(json_encode(array(4, $callId, $uri, '')));

        $this->conn->callError($callId, new Topic($uri));
    }

    public function testDetailedCallError() {
        $callId = uniqid();
        $uri    = 'http://example.com/end/point';
        $desc   = 'beep boop beep';
        $detail = 'Error: Too much awesome';

        $this->mock->expects($this->once())->method('send')->with(json_encode(array(4, $callId, $uri, $desc, $detail)));

        $this->conn->callError($callId, $uri, $desc, $detail);
    }

    public function testPrefix() {
        $shortOut = 'outgoing';
        $longOut  = 'http://example.com/outgoing';

        $this->mock->expects($this->once())->method('send')->with(json_encode(array(1, $shortOut, $longOut)));

        $this->conn->prefix($shortOut, $longOut);
    }

    public function testGetUriWhenNoCurieGiven() {
        $uri  = 'http://example.com/noshort';

        $this->assertEquals($uri, $this->conn->getUri($uri));
    }

    public function testClose() {
        $mock = $this->getMock('\\Ratchet\\ConnectionInterface');
        $conn = new WampConnection($mock);

        $mock->expects($this->once())->method('close');

        $conn->close();
    }
}
