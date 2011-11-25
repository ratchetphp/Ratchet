<?php
namespace Ratchet\Tests\Resource;
use Ratchet\Resource\Connection;
use Ratchet\Tests\Mock\FakeSocket;

/**
 * @covers Ratchet\Resource\Connection
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Ratchet\Tests\Mock\FakeSocket
     */
    protected $_fs;

    /**
     * @var Ratchet\Resource\Connection
     */
    protected $_c;

    public function setUp() {
        $this->_fs = new FakeSocket;
        $this->_c  = new Connection($this->_fs);
    }

    public static function keyAndValProvider() {
        return array(
            array('hello', 'world')
          , array('herp',  'derp')
          , array('depth', array('hell', 'yes'))
          , array('moar',  array('hellz' => 'yes'))
        );
    }

    public function testGetSocketReturnsWhatIsSetInConstruct() {
        $this->assertSame($this->_fs, $this->_c->getSocket());
    }

    /**
     * @dataProvider keyAndValProvider
     */
    public function testCanGetWhatIsSet($key, $val) {
        $this->_c->{$key} = $val;
        $this->assertEquals($val, $this->_c->{$key});
    }

    public function testExceptionThrownOnInvalidGet() {
        $this->setExpectedException('InvalidArgumentException');
        $ret = $this->_c->faked;
    }

    // I think I'll be removing this feature from teh lib soon, so this UT will be removed when it fails
    public function testLambdaReturnValueOnGet() {
        $this->_c->lambda = function() { return 'Hello World!'; };
        $this->assertEquals('Hello World!', $this->_c->lambda);
    }

    /**
     * @dataProvider keyAndValProvider
     */
    public function testIssetWorksOnOverloadedVariables($key, $val) {
        $this->_c->{$key} = $val;
        $this->assertTrue(isset($this->_c->{$key}));
    }

    /**
     * @dataProvider keyAndValProvider
     */
    public function testUnsetMakesIssetReturnFalse($key, $val) {
        $this->_c->{$key} = $val;
        unset($this->_c->{$key});
        $this->assertFalse(isset($this->_c->{$key}));
    }
}