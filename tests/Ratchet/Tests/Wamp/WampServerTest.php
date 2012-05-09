<?php
namespace Ratchet\Tests\Wamp;
use Ratchet\Wamp\WampServer;
use Ratchet\Wamp\WampConnection;
use Ratchet\Tests\Mock\Connection;
use Ratchet\Tests\Mock\WampComponent as TestComponent;

/**
 * @covers Ratchet\Wamp\WampServer
 * @covers Ratchet\Wamp\WampServerInterface
 * @covers Ratchet\Wamp\WampConnection
 */
class WampServerTest extends \PHPUnit_Framework_TestCase {
    protected $_comp;

    protected $_app;

    public function setUp() {
        $this->_app  = new TestComponent;
        $this->_comp = new WampServer($this->_app);
    }

    protected function newConn() {
        return new Connection;
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
        $this->setExpectedException('\\Ratchet\\Wamp\\Exception');

        $conn = $this->newConn();
        $this->_comp->onOpen($conn);
        $this->_comp->onMessage($conn, json_encode(array($type)));
    }

    public function testWelcomeMessage() {
        $conn = $this->newConn();

        $this->_comp->onOpen($conn);

        $message = $conn->last['send'];
        $json    = json_decode($message);

        $this->assertEquals(4, count($json));
        $this->assertEquals(0, $json[0]);
        $this->assertTrue(is_string($json[1]));
        $this->assertEquals(1, $json[2]);
    }

    public function testSubscribe() {
        $uri = 'http://example.com';
        $clientMessage = array(5, $uri);

        $conn = $this->newConn();

        $this->_comp->onOpen($conn);
        $this->_comp->onMessage($conn, json_encode($clientMessage));

        $this->assertEquals($uri, $this->_app->last['onSubscribe'][1]);
    }

    public function testUnSubscribe() {
        $uri = 'http://example.com/endpoint';
        $clientMessage = array(6, $uri);

        $conn = $this->newConn();

        $this->_comp->onOpen($conn);
        $this->_comp->onMessage($conn, json_encode($clientMessage));

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

        $conn = $this->newConn();

        $this->_comp->onOpen($conn);
        $this->_comp->onMessage($conn, json_encode($clientMessage));

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

    public function eventProvider() {
        return array(
            array('http://example.com', array('one', 'two'))
          , array('curie', array(array('hello' => 'world', 'herp' => 'derp')))
        );
    }

    /**
     * @dataProvider eventProvider
     */
    public function testEvent($topic, $payload) {
        $conn = new WampConnection($this->newConn());
        $conn->event($topic, $payload);

        $eventString = $conn->last['send'];

        $this->assertSame(array(8, $topic, $payload), json_decode($eventString, true));
    }

    public function testOnClosePropagation() {
        $conn = new Connection;

        $this->_comp->onOpen($conn);
        $this->_comp->onClose($conn);

        $class  = new \ReflectionClass('\\Ratchet\\Wamp\\WampConnection');
        $method = $class->getMethod('getConnection');
        $method->setAccessible(true);

        $check = $method->invokeArgs($this->_app->last['onClose'][0], array());

        $this->assertSame($conn, $check);
    }

    public function testOnErrorPropagation() {
        $conn = new Connection;

        $e = new \Exception('Nope');

        $this->_comp->onOpen($conn);
        $this->_comp->onError($conn, $e);

        $class  = new \ReflectionClass('\\Ratchet\\Wamp\\WampConnection');
        $method = $class->getMethod('getConnection');
        $method->setAccessible(true);

        $check = $method->invokeArgs($this->_app->last['onError'][0], array());

        $this->assertSame($conn, $check);
        $this->assertSame($e, $this->_app->last['onError'][1]);
    }

    public function testPrefix() {
        $conn = new WampConnection($this->newConn());
        $this->_comp->onOpen($conn);

        $shortIn  = 'incoming';
        $longIn   = 'http://example.com/incoming/';

        $this->_comp->onMessage($conn, json_encode(array(1, $shortIn, $longIn)));

        $this->assertEquals($longIn, $conn->WAMP->prefixes[$shortIn]);
        $this->assertEquals($longIn, $conn->getUri($shortIn));
    }

    public function testMessageMustBeJson() {
        $this->setExpectedException('\\Ratchet\\Wamp\\JsonException');

        $conn = new Connection;

        $this->_comp->onOpen($conn);
        $this->_comp->onMessage($conn, 'Hello World!');
    }
}