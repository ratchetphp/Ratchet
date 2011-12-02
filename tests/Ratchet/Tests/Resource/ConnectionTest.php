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

    public static function lambdaProvider() {
        return array(
            array('hello', 'world')
          , array('obj',   new \stdClass)
          , array('arr',   array())
        );
    }

    /**
     * @dataProvider lambdaProvider
     */
    public function testLambdaReturnValueOnGet($key, $val) {
        $fn = function() use ($val) {
            return $val;
        };

        $this->_c->{$key} = $fn;
        $this->assertSame($val, $this->_c->{$key});
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