<?php
namespace Ratchet\Wamp;
use Ratchet\RatchetTestCase;
/**
 * @covers Ratchet\Wamp\Topic
 */
class TopicTest extends RatchetTestCase {
    public function testGetId() {
        $id    = uniqid();
        $topic = new Topic($id);

        $this->assertEquals($id, $topic->getId());
    }

    public function testAddAndCount() {
        $topic = new Topic('merp');

        $topic->add($this->newConn());
        $topic->add($this->newConn());
        $topic->add($this->newConn());

        $this->assertEquals(3, count($topic));
    }

    public function testRemove() {
        $topic   = new Topic('boop');
        $tracked = $this->newConn();

        $topic->add($this->newConn());
        $topic->add($tracked);
        $topic->add($this->newConn());

        $topic->remove($tracked);

        $this->assertEquals(2, count($topic));
    }

    public function testBroadcast() {
        $msg  = 'Hello World!';
        $name = 'Batman';
        $protocol = json_encode(array(8, $name, $msg));

        if ($this->_version() < 6) {
            $first  = $this->_getMock('Ratchet\\Wamp\\WampConnection', array('send'), array($this->_getMock('\\Ratchet\\ConnectionInterface')));
            $second = $this->_getMock('Ratchet\\Wamp\\WampConnection', array('send'), array($this->_getMock('\\Ratchet\\ConnectionInterface')));
        } else {
            $first = $this->getMockBuilder('Ratchet\\Wamp\\WampConnection')->onlyMethods(['send'])->setConstructorArgs([$this->_getMock('\\Ratchet\\ConnectionInterface')])->getMock();
            $second = $this->getMockBuilder('Ratchet\\Wamp\\WampConnection')->onlyMethods(['send'])->setConstructorArgs([$this->_getMock('\\Ratchet\\ConnectionInterface')])->getMock();
        }

        $first->expects($this->once())
              ->method('send')
              ->with($this->equalTo($protocol));

        $second->expects($this->once())
              ->method('send')
              ->with($this->equalTo($protocol));

        $topic = new Topic($name);
        $topic->add($first);
        $topic->add($second);

        $topic->broadcast($msg);
    }

    public function testBroadcastWithExclude() {
        $msg  = 'Hello odd numbers';
        $name = 'Excluding';
        $protocol = json_encode(array(8, $name, $msg));

        if ($this->_version() < 6) {
            $first  = $this->_getMock('Ratchet\\Wamp\\WampConnection', array('send'), array($this->_getMock('\\Ratchet\\ConnectionInterface')));
            $second = $this->_getMock('Ratchet\\Wamp\\WampConnection', array('send'), array($this->_getMock('\\Ratchet\\ConnectionInterface')));
            $third = $this->_getMock('Ratchet\\Wamp\\WampConnection', array('send'), array($this->_getMock('\\Ratchet\\ConnectionInterface')));
        } else {
            $first = $this->getMockBuilder('Ratchet\\Wamp\\WampConnection')->onlyMethods(array('send'))->setConstructorArgs(array($this->_getMock('\\Ratchet\\ConnectionInterface')))->getMock();
            $second = $this->getMockBuilder('Ratchet\\Wamp\\WampConnection')->onlyMethods(array('send'))->setConstructorArgs(array($this->_getMock('\\Ratchet\\ConnectionInterface')))->getMock();
            $third = $this->getMockBuilder('Ratchet\\Wamp\\WampConnection')->onlyMethods(array('send'))->setConstructorArgs(array($this->_getMock('\\Ratchet\\ConnectionInterface')))->getMock();
        }

        $first->expects($this->once())
            ->method('send')
            ->with($this->equalTo($protocol));

        $second->expects($this->never())->method('send');

        $third->expects($this->once())
            ->method('send')
            ->with($this->equalTo($protocol));

        $topic = new Topic($name);
        $topic->add($first);
        $topic->add($second);
        $topic->add($third);

        $topic->broadcast($msg, array($second->WAMP->sessionId));
    }

    public function testBroadcastWithEligible() {
        $msg  = 'Hello white list';
        $name = 'Eligible';
        $protocol = json_encode(array(8, $name, $msg));

        if ($this->_version() < 6) {
            $first  = $this->_getMock('Ratchet\\Wamp\\WampConnection', array('send'), array($this->_getMock('\\Ratchet\\ConnectionInterface')));
            $second = $this->_getMock('Ratchet\\Wamp\\WampConnection', array('send'), array($this->_getMock('\\Ratchet\\ConnectionInterface')));
            $third = $this->_getMock('Ratchet\\Wamp\\WampConnection', array('send'), array($this->_getMock('\\Ratchet\\ConnectionInterface')));
        } else {
            $first = $this->getMockBuilder('Ratchet\\Wamp\\WampConnection')->onlyMethods(array('send'))->setConstructorArgs(array($this->_getMock('\\Ratchet\\ConnectionInterface')))->getMock();
            $second = $this->getMockBuilder('Ratchet\\Wamp\\WampConnection')->onlyMethods(array('send'))->setConstructorArgs(array($this->_getMock('\\Ratchet\\ConnectionInterface')))->getMock();
            $third = $this->getMockBuilder('Ratchet\\Wamp\\WampConnection')->onlyMethods(array('send'))->setConstructorArgs(array($this->_getMock('\\Ratchet\\ConnectionInterface')))->getMock();
        }

        $first->expects($this->once())
            ->method('send')
            ->with($this->equalTo($protocol));

        $second->expects($this->never())->method('send');

        $third->expects($this->once())
            ->method('send')
            ->with($this->equalTo($protocol));

        $topic = new Topic($name);
        $topic->add($first);
        $topic->add($second);
        $topic->add($third);

        $topic->broadcast($msg, array(), array($first->WAMP->sessionId, $third->WAMP->sessionId));
    }

    public function testIterator() {
        $first  = $this->newConn();
        $second = $this->newConn();
        $third  = $this->newConn();

        $topic  = new Topic('Joker');
        $topic->add($first)->add($second)->add($third);

        $check = array($first, $second, $third);

        foreach ($topic as $mock) {
            $this->assertNotSame(false, array_search($mock, $check));
        }
    }

    public function testToString() {
        $name  = 'Bane';
        $topic = new Topic($name);

        $this->assertEquals($name, (string)$topic);
    }

    public function testDoesHave() {
        $conn  = $this->newConn();
        $topic = new Topic('Two Face');
        $topic->add($conn);

        $this->assertTrue($topic->has($conn));
    }

    public function testDoesNotHave() {
        $conn  = $this->newConn();
        $topic = new Topic('Alfred');

        $this->assertFalse($topic->has($conn));
    }

    public function testDoesNotHaveAfterRemove() {
        $conn  = $this->newConn();
        $topic = new Topic('Ras');

        $topic->add($conn)->remove($conn);

        $this->assertFalse($topic->has($conn));
    }

    protected function newConn() {
        return new WampConnection($this->_getMock('\\Ratchet\\ConnectionInterface'));
    }
}
