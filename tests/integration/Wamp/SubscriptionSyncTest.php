<?php
namespace Ratchet\Wamp;
use Ratchet\Wamp\WampServer;
use Ratchet\ConnectionInterface;

class SubscriptionSyncTest extends \PHPUnit_Framework_TestCase {
    public function testRemoveFromTopicRemovesFromManager() {
        $conn  = $this->getMock('Ratchet\ConnectionInterface');
        $app   = $this->getMock('Ratchet\Wamp\WampServerInterface');

        $wamp = new WampServer($app);
        $wamp->onOpen($conn);

        $wampRef = new \ReflectionClass($wamp);
        $protoPropRef= $wampRef->getProperty('wampProtocol');
        $protoPropRef->setAccessible(true);
        $proto = $protoPropRef->getValue($wamp);
        $protoRef = new \ReflectionClass($proto);
        $tmPropRef = $protoRef->getProperty('_decorating');
        $tmPropRef->setAccessible(true);
        $topicManager = $tmPropRef->getValue($proto);
        $tmRef = new \ReflectionClass($topicManager);
        $tmPropRef = $tmRef->getProperty('topicLookup');
        $tmPropRef->setAccessible(true);

        $wamp->onMessage($conn, json_encode(array('5', 'topic1')));
        $wamp->onMessage($conn, json_encode(array('5', 'topic2')));

        $self = $this;
        $app->expects($this->any())->method('onSubscribe')->will($this->returnCallback(
            function(ConnectionInterface $conn, $topic) use ($self, $tmPropRef, $topicManager) {
                $topic->remove($conn);

                $topics = $tmPropRef->getValue($topicManager);
                $self->assertEquals(2, count($topics));
            }
        ));

        $wamp->onMessage($conn, json_encode(array('5', 'testTopic')));
    }
}