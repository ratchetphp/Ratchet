<?php
namespace Ratchet;
use Ratchet\Mock\ConnectionDecorator;

/**
 * @covers Ratchet\AbstractConnectionDecorator
 * @covers Ratchet\ConnectionInterface
 */
class AbstractConnectionDecoratorTest extends \PHPUnit_Framework_TestCase {
    protected $mock;
    protected $l1;
    protected $l2;

    public function setUp() {
        $this->mock = $this->getMock('\Ratchet\ConnectionInterface');
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
        $class  = new \ReflectionClass('\\Ratchet\\AbstractConnectionDecorator');
        $method = $class->getMethod('getConnection');
        $method->setAccessible(true);

        $conn = $method->invokeArgs($this->l1, array());

        $this->assertSame($this->mock, $conn);
    }

    public function testGetConnectionLevel2() {
        $class  = new \ReflectionClass('\\Ratchet\\AbstractConnectionDecorator');
        $method = $class->getMethod('getConnection');
        $method->setAccessible(true);

        $conn = $method->invokeArgs($this->l2, array());

        $this->assertSame($this->l1, $conn);
    }

    public function testWrapperCanStoreSelfInDecorator() {
        $this->mock->decorator = $this->l1;

        $this->assertSame($this->l1, $this->l2->decorator);
    }

    public function testDecoratorRecursion() {
        $this->mock->decorator = new \stdClass;
        $this->mock->decorator->conn = $this->l1;

        $this->assertSame($this->l1, $this->mock->decorator->conn);
        $this->assertSame($this->l1, $this->l1->decorator->conn);
        $this->assertSame($this->l1, $this->l2->decorator->conn);
    }

    public function testDecoratorRecursionLevel2() {
        $this->mock->decorator = new \stdClass;
        $this->mock->decorator->conn = $this->l2;

        $this->assertSame($this->l2, $this->mock->decorator->conn);
        $this->assertSame($this->l2, $this->l1->decorator->conn);
        $this->assertSame($this->l2, $this->l2->decorator->conn);

        // just for fun
        $this->assertSame($this->l2, $this->l2->decorator->conn->decorator->conn->decorator->conn);
    }

    public function testWarningGettingNothing() {
        $this->setExpectedException('PHPUnit_Framework_Error');
        $var = $this->mock->nonExistant;
    }

    public function testWarningGettingNothingLevel1() {
        $this->setExpectedException('PHPUnit_Framework_Error');
        $var = $this->l1->nonExistant;
    }

    public function testWarningGettingNothingLevel2() {
        $this->setExpectedException('PHPUnit_Framework_Error');
        $var = $this->l2->nonExistant;
    }
}
