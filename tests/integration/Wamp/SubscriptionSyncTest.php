<?php
namespace Ratchet\Wamp;
use Ratchet\Wamp\WampServer;
use Ratchet\ConnectionInterface;

class SubscriptionSyncTest extends \PHPUnit_Framework_TestCase {
    protected $_conn;
    protected $_app;
    protected $_wamp;

    protected $_tmPropRef;
    protected $_topicManager;

    public function setUp() {
        $this->_conn  = $this->getMock('Ratchet\ConnectionInterface');
        $this->_app   = $this->getMock('Ratchet\Wamp\WampServerInterface');

        $this->_wamp = new WampServer($this->_app);
        $this->_wamp->onOpen($this->_conn);

        $wampRef = new \ReflectionClass($this->_wamp);
        $protoPropRef= $wampRef->getProperty('wampProtocol');
        $protoPropRef->setAccessible(true);
        $proto = $protoPropRef->getValue($this->_wamp);
        $protoRef = new \ReflectionClass($proto);
        $this->_tmPropRef = $protoRef->getProperty('_decorating');
        $this->_tmPropRef->setAccessible(true);
        $this->_topicManager = $this->_tmPropRef->getValue($proto);
        $tmRef = new \ReflectionClass($this->_topicManager);
        $this->_tmPropRef = $tmRef->getProperty('topicLookup');
        $this->_tmPropRef->setAccessible(true);

        $this->_wamp->onMessage($this->_conn, json_encode(array('5', 'topic1')));
        $this->_wamp->onMessage($this->_conn, json_encode(array('5', 'topic2')));
    }

    public function testRemoveFromTopicRemovesFromManager() {
        $self         = $this;
        $tmPropRef    = $this->_tmPropRef;
        $topicManager = $this->_topicManager;

        $this->_app->expects($this->any())->method('onSubscribe')->will($this->returnCallback(
            function(ConnectionInterface $conn, $topic) use ($self, $tmPropRef, $topicManager) {
                $topic->remove($conn);


                $topics = $tmPropRef->getValue($topicManager);
                $self->assertEquals(2, count($topics));

            }
        ));

        $this->_wamp->onMessage($this->_conn, json_encode(array('5', 'testTopic')));
    }

    public function testRemoveFromConnectionRemovesFromManager() {
        $self         = $this;
        $tmPropRef    = $this->_tmPropRef;
        $topicManager = $this->_topicManager;

        $this->_app->expects($this->any())->method('onSubscribe')->will($this->returnCallback(
            function(ConnectionInterface $conn, $topic) use ($self, $tmPropRef, $topicManager) {
                $conn->WAMP->subscriptions->detach($topic);

                $topics = $tmPropRef->getValue($topicManager);
                $self->assertEquals(2, count($topics));

            }
        ));

        $this->_wamp->onMessage($this->_conn, json_encode(array('5', 'testTopic')));
    }
}