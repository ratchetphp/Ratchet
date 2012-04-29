<?php
namespace Ratchet\Tests\Component\WAMP;
use Ratchet\Component\WAMP\WAMPServerComponent;
use Ratchet\Resource\Connection;
use Ratchet\Tests\Mock\FakeSocket;
use Ratchet\Tests\Mock\WAMPComponent as TestComponent;
use Ratchet\Component\WAMP\Command\Action\CallResult;
use Ratchet\Component\WAMP\Command\Action\CallError;
use Ratchet\Component\WAMP\Command\Action\Event;

/**
 * @covers Ratchet\Component\WAMP\WAMPServerComponent
 * @covers Ratchet\Component\WAMP\WAMPServerComponentInterface
 */
class WAMPServerComponentTest extends \PHPUnit_Framework_TestCase {
    protected $_comp;

    protected $_app;

    public function setUp() {
        $this->_app  = new TestComponent;
        $this->_comp = new WAMPServerComponent($this->_app);
    }

    protected function newConn() {
        return new Connection(new FakeSocket);
    }

    public function invalidMessageProvider() {
        return array(
            array(0)
          , array(3)
          , array(4)
          , array(8)
          , array(9)
        );
    }

    /**
     * @dataProvider invalidMessageProvider
     */
    public function testInvalidMessages($type) {
        $this->setExpectedException('\\Ratchet\\Component\\WAMP\\Exception');

        $this->_comp->onMessage($this->newConn(), json_encode(array($type)));
    }

    /**
     * @covers Ratchet\Component\WAMP\Command\Action\Welcome
     */
    public function testWelcomeMessage() {
        $conn = new Connection(new FakeSocket);

        $return  = $this->_comp->onOpen($conn);
        $action  = $return->pop();
        $message = $action->getMessage();
        $json    = json_decode($message);

        $this->assertEquals(4, count($json));
        $this->assertEquals(0, $json[0]);
        $this->assertTrue(is_string($json[1]));
        $this->assertEquals(1, $json[2]);
    }

    public function testSubscribe() {
        $uri = 'http://example.com';
        $clientMessage = array(5, $uri);

        $this->_comp->onMessage($this->newConn(), json_encode($clientMessage));

        $this->assertEquals($uri, $this->_app->last['onSubscribe'][1]);
    }

    public function testUnSubscribe() {
        $uri = 'http://example.com/endpoint';
        $clientMessage = array(6, $uri);

        $this->_comp->onMessage($this->newConn(), json_encode($clientMessage));

        $this->assertEquals($uri, $this->_app->last['onUnSubscribe'][1]);
    }

    public function callProvider() {
        return array(
            array(2, 'a', 'b')
          , array(2, array('a', 'b'))
          , array(1, 'one')
          , array(3, 'one', 'two', 'three')
          , array(3, array('un', 'deux', 'trois'))
          , array(2, 'hi', array('hello', 'world'))
          , array(2, array('hello', 'world'), 'hi')
          , array(2, array('hello' => 'world', 'herp' => 'derp'))
        );
    }

    /**
     * @dataProvider callProvider
     */
    public function testCall() {
        $args     = func_get_args();
        $paramNum = array_shift($args);

        $uri = 'http://example.com/endpoint/' . rand(1, 100);
        $id  = uniqid();
        $clientMessage = array_merge(array(2, $id, $uri), $args);

        $this->_comp->onMessage($this->newConn(), json_encode($clientMessage));

        $this->assertEquals($id,  $this->_app->last['onCall'][1]);
        $this->assertEquals($uri, $this->_app->last['onCall'][2]);

        $this->assertEquals($paramNum, count($this->_app->last['onCall'][3]));
    }

    public function publishProvider() {
        return array(
        );
    }

    /**
     * @dataProvider publishProvider
     */
    public function TODOtestPublish() {
        
    }

    /**
     * @covers Ratchet\Component\WAMP\Command\Action\CallResult
     */
    public function testCallResponse() {
        $result = new CallResult($this->newConn());

        $callId = uniqid();
        $data   = array('hello' => 'world', 'herp' => 'derp');

        $result->setResult($callId, $data);
        $resultString = $result->getMessage();

        $this->assertEquals(array(3, $callId, $data), json_decode($resultString, true));
    }

    /**
     * @covers Ratchet\Component\WAMP\Command\Action\CallError
     */
    public function testCallError() {
        $error = new CallError($this->newConn());

        $callId = uniqid();
        $uri    = 'http://example.com/end/point';

        $error->setError($callId, $uri);
        $resultString = $error->getMessage();

        $this->assertEquals(array(4, $callId, $uri, ''), json_decode($resultString, true));
    }

    /**
     * @covers Ratchet\Component\WAMP\Command\Action\CallError
     */
    public function testDetailedCallError() {
        $error = new CallError($this->newConn());

        $callId = uniqid();
        $uri    = 'http://example.com/end/point';
        $desc   = 'beep boop beep';
        $detail = 'Error: Too much awesome';

        $error->setError($callId, $uri, $desc, $detail);
        $resultString = $error->getMessage();

        $this->assertEquals(array(4, $callId, $uri, $desc, $detail), json_decode($resultString, true));
    }

    public function eventProvider() {
        return array(
            array('http://example.com', array('one', 'two'))
          , array('curie', array(array('hello' => 'world', 'herp' => 'derp')))
        );
    }

    /**
     * @dataProvider eventProvider
     * @covers Ratchet\Component\WAMP\Command\Action\Event
     */
    public function testEvent($topic, $payload) {
        $event = new Event($this->newConn());
        $event->setEvent($topic, $payload);

        $eventString = $event->getMessage();

        $this->assertSame(array(8, $topic, $payload), json_decode($eventString, true));
    }

    public function testOnClosePropagation() {
        $conn = $this->newConn();

        $this->_comp->onClose($conn);

        $this->assertSame($conn, $this->_app->last['onClose'][0]);
    }

    public function testOnErrorPropagation() {
        $conn = $this->newConn();

        try {
            throw new \Exception('Nope');
        } catch (\Exception $e) {
        }

        $this->_comp->onError($conn, $e);

        $this->assertSame($conn, $this->_app->last['onError'][0]);
        $this->assertSame($e, $this->_app->last['onError'][1]);
    }

    /**
     * @covers Ratchet\Component\WAMP\Command\Action\Prefix
     */
    public function testPrefix() {
        $conn = $this->newConn();
        $this->_comp->onOpen($conn);

        $shortOut = 'outgoing';
        $longOut  = 'http://example.com/outoing';

        $shortIn = 'incoming';
        $shortIn = 'http://example.com/incoming/';

        $this->assertTrue(is_callable($conn->WAMP->addPrefix));

        $cb = $conn->WAMP->addPrefix;
        $cb($shortOut, $longOut);

        $return  = $this->_comp->onMessage($conn, json_encode(array(1, $shortIn, $shortOut)));
        $command = $return->pop();

        $this->assertInstanceOf('Ratchet\\Component\\WAMP\\Command\\Action\\Prefix', $command);
        $this->assertEquals($shortOut, $command->getCurie());
        $this->assertEquals($longOut, $command->getUri());

        $this->assertEquals(array(1, $shortOut, $longOut), json_decode($command->getMessage()));
    }
}