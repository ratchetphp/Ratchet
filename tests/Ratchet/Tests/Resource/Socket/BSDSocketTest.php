<?php
namespace Ratchet\Tests\Resource\Socket;
use Ratchet\Tests\Mock\FakeSocket as Socket;
use Ratchet\Resource\Socket\BSDSocket as RealSocket;

/**
 * @covers Ratchet\Resource\Socket\BSDSocket
 */
class BSDSocketTest extends \PHPUnit_Framework_TestCase {
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

    /* (1): I may or may not re-enable this test (need to add code back to FakeSocket), not sure if I'll keep this feature at all
    public function testGetDefaultConfigForConstruct() {
        $ref_conf = static::getMethod('getConfig');
        $config   = $ref_conf->invokeArgs($this->_socket, array());

        $this->assertEquals(array_values(Socket::$_defaults), $config);
    }
    /**/

    public function testInvalidConstructorArguments() {
        $this->setExpectedException('\\Ratchet\\Resource\\Socket\\BSDSocketException');
        $socket = new RealSocket('invalid', 'param', 'derp');
    }

    public function testConstructAndCallByOpenAndClose() {
        $socket = new RealSocket();
        $socket->close();
    }

    public function asArrayProvider() {
        return array(
            array(array('hello' => 'world'), array('hello' => 'world'))
          , array(null, null)
          , array(array('hello' => 'world'), new \ArrayObject(array('hello' => 'world')))
        );
    }

    /**
     * (1)
     * @dataProvider asArrayProvider
     * /
    public function testMethodMungforselectReturnsExpectedValues($output, $input) {
        $method = static::getMethod('mungForSelect');
        $return = $method->invokeArgs($this->_socket, array($input));

        $this->assertEquals($return, $output);
    }

    public function NOPEtestMethodMungforselectRejectsNonTraversable() {
        $this->setExpectedException('\\InvalidArgumentException');
        $method = static::getMethod('mungForSelect');
        $method->invokeArgs($this->_socket, array('I am upset with PHP ATM'));
    }
    */
}