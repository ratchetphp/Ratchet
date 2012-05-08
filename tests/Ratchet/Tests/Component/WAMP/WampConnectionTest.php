<?php
namespace Ratchet\Tests\Component\WAMP;
use Ratchet\Component\WAMP\WampConnection;
use Ratchet\Tests\Mock\Connection;

/**
 * @covers Ratchet\Component\WAMP\WampConnection
 */
class WampConnectionTest extends \PHPUnit_Framework_TestCase {
    public function testCallResult() {
        $conn  = new Connection;
        $decor = new WampConnection($conn);

        $callId = uniqid();
        $data   = array('hello' => 'world', 'herp' => 'derp');


        $decor->callResult($callId, $data);
        $resultString = $conn->last['send'];

        $this->assertEquals(array(3, $callId, $data), json_decode($resultString, true));
    }

    public function testCallError() {
        $conn  = new Connection;
        $decor = new WampConnection($conn);

        $callId = uniqid();
        $uri    = 'http://example.com/end/point';

        $decor->callError($callId, $uri);
        $resultString = $conn->last['send'];

        $this->assertEquals(array(4, $callId, $uri, ''), json_decode($resultString, true));
    }

    public function testDetailedCallError() {
        $conn  = new Connection;
        $decor = new WampConnection($conn);

        $callId = uniqid();
        $uri    = 'http://example.com/end/point';
        $desc   = 'beep boop beep';
        $detail = 'Error: Too much awesome';

        $decor->callError($callId, $uri, $desc, $detail);
        $resultString = $conn->last['send'];

        $this->assertEquals(array(4, $callId, $uri, $desc, $detail), json_decode($resultString, true));
    }

    public function testPrefix() {
        $conn = new WampConnection(new Connection);

        $shortOut = 'outgoing';
        $longOut  = 'http://example.com/outoing';

        $conn->prefix($shortOut, $longOut);
    }

    public function testGetUriWhenNoCurieGiven() {
        $conn = new WampConnection(new Connection);
        $uri  = 'http://example.com/noshort';

        $this->assertEquals($uri, $conn->getUri($uri));
    }
}