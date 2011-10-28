<?php
namespace Ratchet\Tests;
use Ratchet\Tests\Mock\FakeSocket as Socket;
use Ratchet\Socket as RealSocket;
use Ratchet\Tests\Mock\Protocol;
use Ratchet\Tests\Mock\Application as TestApp;

/**
 * @covers Ratchet\Socket
 */
class SocketTest extends \PHPUnit_Framework_TestCase {
    protected $_socket;

    protected static function getMethod($name) {
        $class  = new \ReflectionClass('\\Ratchet\\Tests\\Mock\\FakeSocket');
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
        $config   = $ref_conf->invokeArgs($this->_socket, array());

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

    public function testConstructionFromProtocolInterfaceConfig() {
        $protocol = new Protocol(new TestApp);
        $socket   = Socket::createFromConfig($protocol);

        $this->assertInstanceOf('\\Ratchet\\Socket', $socket);
    }

    public function testCreationFromConfigOutputMatchesInput() {
        $protocol = new Protocol(new TestApp);
        $socket   = Socket::createFromConfig($protocol);
        $config   = $protocol::getDefaultConfig();

        // change this to array_filter late
        unset($config['options']);

        $this->assertAttributeEquals($config, '_arguments', $socket);
    }

    public function asArrayProvider() {
        return Array(
            Array(Array('hello' => 'world'), Array('hello' => 'world'))
          , Array(null, null)
          , Array(Array('hello' => 'world'), new \ArrayObject(Array('hello' => 'world')))
        );
    }

    /**
     * @dataProvider asArrayProvider
     */
    public function testMethodMungforselectReturnsExpectedValues($output, $input) {
        $method = static::getMethod('mungForSelect');
        $return = $method->invokeArgs($this->_socket, Array($input));

        $this->assertEquals($return, $output);
    }

    public function testMethodMungforselectRejectsNonTraversable() {
        $this->setExpectedException('\\InvalidArgumentException');
        $method = static::getMethod('mungForSelect');
        $method->invokeArgs($this->_socket, Array('I am upset with PHP ATM'));
    }
}