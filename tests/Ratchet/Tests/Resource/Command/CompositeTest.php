<?php
namespace Ratchet\Tests\Resource\Command;
use Ratchet\Resource\Command\Composite;
use Ratchet\Resource\Connection;
use Ratchet\Tests\Mock\FakeSocket;
use Ratchet\Resource\Command\Action\Null as NullAction;

/**
 * @covers Ratchet\Resource\Command\Composite
 */
class CompositeTestnope extends \PHPUnit_Framework_TestCase {
    protected $_comp;

    public function setUp() {
        $this->_comp = new Composite;
    }

    protected function newNull() {
        return new NullAction(new Connection(new FakeSocket));
    }

    public function testCanEnqueueNull() {
        $count = $this->_comp->count();

        $this->_comp->enqueue(null);

        $this->assertEquals($count, $this->_comp->count());
    }

    public function testEnqueueCommand() {
        $count = $this->_comp->count();

        $this->_comp->enqueue($this->newNull());

        $this->assertEquals($count + 1, $this->_comp->count());
    }

    public function badEnqueueProviders() {
        return array(
            array(array())
          , array('string')
        );
    }

    /**
     * @dataProvider badEnqueueProviders
     */
    public function testCanNotPassOtherThings($object) {
        $this->setExpectedException('InvalidArgumentException');

        $this->_comp->enqueue($object);
    }

    public function testCompositeComposite() {
        $compTwo = new Composite;
        $compTwo->enqueue($this->newNull());
        $compTwo->enqueue($this->newNull());

        $this->_comp->enqueue($this->newNull());
        $this->_comp->enqueue($compTwo);

        $this->assertEquals(3, $this->_comp->count());
    }
}