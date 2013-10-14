<?php
namespace Ratchet\WebSocket;
use Ratchet\WebSocket\WsServer;
use Ratchet\Mock\Component as MockComponent;

/**
 * @covers Ratchet\WebSocket\WsServer
 * @covers Ratchet\ComponentInterface
 * @covers Ratchet\MessageComponentInterface
 */
class WsServerTest extends \PHPUnit_Framework_TestCase {
    protected $comp;

    protected $serv;

    public function setUp() {
        $this->comp = new MockComponent;
        $this->serv = new WsServer($this->comp);
    }

    public function testIsSubProtocolSupported() {
        $this->comp->protocols = array('hello', 'world');

        $this->assertTrue($this->serv->isSubProtocolSupported('hello'));
        $this->assertFalse($this->serv->isSubProtocolSupported('nope'));
    }

    public function protocolProvider() {
        return array(
            array('hello', array('hello', 'world'), array('hello', 'world'))
          , array('', array('hello', 'world'), array('wamp'))
          , array('', array(), null)
          , array('wamp', array('hello', 'wamp', 'world'), array('herp', 'derp', 'wamp'))
          , array('wamp', array('wamp'), array('wamp'))
        );
    }

    /**
     * @dataProvider protocolProvider
     */
    public function testGetSubProtocolString($expected, $supported, $requested) {
        $this->comp->protocols = $supported;
        $req = (null === $requested ? $requested : new \ArrayIterator($requested));

        $class  = new \ReflectionClass('Ratchet\\WebSocket\\WsServer');
        $method = $class->getMethod('getSubProtocolString');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invokeArgs($this->serv, array($req)));
    }
}