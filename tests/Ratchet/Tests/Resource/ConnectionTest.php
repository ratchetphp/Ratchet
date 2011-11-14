<?php
namespace Ratchet\Tests\Resource;
use Ratchet\Resource\Connection;
use Ratchet\Tests\Mock\FakeSocket;

/**
 * @covers Ratchet\Resource\Connection
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase {
    protected $_c;

    public function setUp() {
        $this->_c = new Connection(new FakeSocket);
    }

    public function testCanGetWhatIsSet() {
        $key = 'hello';
        $val = 'world';

        $this->_c->{$key} = $val;
        $this->assertEquals($val, $this->_c->{$key});
    }

    public function testExceptionThrownOnInvalidGet() {
        $this->setExpectedException('InvalidArgumentException');
        $ret = $this->_c->faked;
    }

    public function testLambdaReturnValueOnGet() {
        $this->markTestIncomplete();
    }
}