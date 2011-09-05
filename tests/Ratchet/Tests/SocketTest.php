<?php
namespace Ratchet\Tests;
use Ratchet\Tests\Mock\Socket;
use Ratchet\Socket as RealSocket;

/**
 * @covers Ratchet\Socket
 */
class SocketTest extends \PHPUnit_Framework_TestCase {
    protected $_socket;

    protected static function getMethod($name) {
        $class  = new \ReflectionClass('\\Ratchet\\Tests\\Mock\\Socket');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public function setUp() {
        $this->_socket = new Socket();
    }

/*
    public function testWhatGoesInConstructComesOut() {
        $this->assertTrue(false);
    }
*/

    public function testGetDefaultConfigForConstruct() {
        $ref_conf = static::getMethod('getConfig');
        $config   = $ref_conf->invokeArgs($this->_socket, Array());

        $this->assertEquals(array_values(Socket::$_defaults), $config);
    }

    public function testInvalidConstructorArguments() {
        $this->setExpectedException('\\Ratchet\\Exception');
        $socket = new RealSocket('invalid', 'param', 'derp');
    }

    public function testConstructAndCallByOpenAndClose() {
        $socket = new RealSocket();
        $socket->close();
    }

    public function testInvalidSocketCall() {
        $this->setExpectedException('\\BadMethodCallException');
        $this->_socket->fake_method();
    }
}