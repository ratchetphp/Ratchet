<?php
namespace Ratchet\Tests\Resource;
use Ratchet\Tests\Mock\ConnectionDecorator;
use Ratchet\Tests\Mock\Connection;

/**
 * @covers Ratchet\Resource\AbstractConnectionDecorator
 */
class AbstractConnectionDecoratorTest extends \PHPUnit_Framework_TestCase {
    protected $mock;
    protected $l1;
    protected $l2;

    public function setUp() {
        $this->mock = new Connection;
        $this->l1   = new ConnectionDecorator($this->mock);
        $this->l2   = new ConnectionDecorator($this->l1);
    }

    public function testGet() {
        $var = 'hello';
        $val = 'world';

        $this->mock->$var = $val;

        $this->assertEquals($val, $this->l1->$var);
        $this->assertEquals($val, $this->l2->$var);
    }

    public function testSet() {
        $var = 'Chris';
        $val = 'Boden';

        $this->l1->$var = $val;

        $this->assertEquals($val, $this->mock->$var);
    }

    public function testSetLevel2() {
        $var = 'Try';
        $val = 'Again';

        $this->l2->$var = $val;

        $this->assertEquals($val, $this->mock->$var);
    }

    public function testIsSetTrue() {
        $var = 'PHP';
        $val = 'Ratchet';

        $this->mock->$var = $val;

        $this->assertTrue(isset($this->l1->$var));
        $this->assertTrue(isset($this->l2->$var));
    }

    public function testIsSetFalse() {
        $var = 'herp';
        $val = 'derp';

        $this->assertFalse(isset($this->l1->$var));
        $this->assertFalse(isset($this->l2->$var));
    }

    public function testUnset() {
        $var = 'Flying';
        $val = 'Monkey';

        $this->mock->$var = $val;
        unset($this->l1->$var);

        $this->assertFalse(isset($this->mock->$var));
    }

    public function testUnsetLevel2() {
        $var = 'Flying';
        $val = 'Monkey';

        $this->mock->$var = $val;
        unset($this->l2->$var);

        $this->assertFalse(isset($this->mock->$var));
    }

    public function testGetConnection() {
        $class  = new \ReflectionClass('\\Ratchet\\Resource\\AbstractConnectionDecorator');
        $method = $class->getMethod('getConnection');
        $method->setAccessible(true);

        $conn = $method->invokeArgs($this->l1, array());

        $this->assertSame($this->mock, $conn);
    }

    public function testGetConnectionLevel2() {
        $class  = new \ReflectionClass('\\Ratchet\\Resource\\AbstractConnectionDecorator');
        $method = $class->getMethod('getConnection');
        $method->setAccessible(true);

        $conn = $method->invokeArgs($this->l2, array());

        $this->assertSame($this->l1, $conn);
    }

    public function testWarningGettingNothing() {
        $this->markTestSkipped('Functionality not in class yet');
    }
}