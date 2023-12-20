<?php

namespace Ratchet;

use PHPUnit\Framework\TestCase;
use Ratchet\Mock\ConnectionDecorator;

/**
 * @covers Ratchet\AbstractConnectionDecorator
 * @covers Ratchet\ConnectionInterface
 */
class AbstractConnectionDecoratorTest extends TestCase
{
    protected ConnectionInterface $mock;

    protected ConnectionDecorator $l1;

    protected ConnectionDecorator $l2;

    public function setUp(): void
    {
        $this->mock = $this->getMockBuilder(ConnectionInterface::class)->getMock();
        $this->l1 = new ConnectionDecorator($this->mock);
        $this->l2 = new ConnectionDecorator($this->l1);
    }

    public function testGet(): void
    {
        $var = 'hello';
        $val = 'world';

        $this->mock->$var = $val;

        $this->assertEquals($val, $this->l1->$var);
        $this->assertEquals($val, $this->l2->$var);
    }

    public function testSet(): void
    {
        $var = 'Chris';
        $val = 'Boden';

        $this->l1->$var = $val;

        $this->assertEquals($val, $this->mock->$var);
    }

    public function testSetLevel2(): void
    {
        $var = 'Try';
        $val = 'Again';

        $this->l2->$var = $val;

        $this->assertEquals($val, $this->mock->$var);
    }

    public function testIsSetTrue(): void
    {
        $var = 'PHP';
        $val = 'Ratchet';

        $this->mock->$var = $val;

        $this->assertTrue(isset($this->l1->$var));
        $this->assertTrue(isset($this->l2->$var));
    }

    public function testIsSetFalse(): void
    {
        $var = 'herp';

        $this->assertFalse(isset($this->l1->$var));
        $this->assertFalse(isset($this->l2->$var));
    }

    public function testUnset(): void
    {
        $var = 'Flying';
        $val = 'Monkey';

        $this->mock->$var = $val;
        unset($this->l1->$var);

        $this->assertFalse(isset($this->mock->$var));
    }

    public function testUnsetLevel2(): void
    {
        $var = 'Flying';
        $val = 'Monkey';

        $this->mock->$var = $val;
        unset($this->l2->$var);

        $this->assertFalse(isset($this->mock->$var));
    }

    public function testGetConnection(): void
    {
        $class = new \ReflectionClass(AbstractConnectionDecorator::class);
        $method = $class->getMethod('getConnection');
        $method->setAccessible(true);

        $connection = $method->invokeArgs($this->l1, []);

        $this->assertSame($this->mock, $connection);
    }

    public function testGetConnectionLevel2(): void
    {
        $class = new \ReflectionClass(AbstractConnectionDecorator::class);
        $method = $class->getMethod('getConnection');
        $method->setAccessible(true);

        $connection = $method->invokeArgs($this->l2, []);

        $this->assertSame($this->l1, $connection);
    }

    public function testWrapperCanStoreSelfInDecorator(): void
    {
        $this->mock->decorator = $this->l1;

        $this->assertSame($this->l1, $this->l2->decorator);
    }

    public function testDecoratorRecursion(): void
    {
        $this->mock->decorator = new \stdClass;
        $this->mock->decorator->conn = $this->l1;

        $this->assertSame($this->l1, $this->mock->decorator->conn);
        $this->assertSame($this->l1, $this->l1->decorator->conn);
        $this->assertSame($this->l1, $this->l2->decorator->conn);
    }

    public function testDecoratorRecursionLevel2(): void
    {
        $this->mock->decorator = new \stdClass;
        $this->mock->decorator->conn = $this->l2;

        $this->assertSame($this->l2, $this->mock->decorator->conn);
        $this->assertSame($this->l2, $this->l1->decorator->conn);
        $this->assertSame($this->l2, $this->l2->decorator->conn);

        // just for fun
        $this->assertSame($this->l2, $this->l2->decorator->conn->decorator->conn->decorator->conn);
    }
}
