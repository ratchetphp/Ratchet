<?php
namespace Ratchet\Wamp;

/**
 * A topic/channel containing connections that have subscribed to it
 */
class Topic implements \IteratorAggregate, \Countable {
    private $id;

    private $subscribers;

    /**
     * @param string Unique ID for this object
     */
    public function __construct($topicId) {
        $this->id = $topicId;
        $this->subscribers = new \SplObjectStorage;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
      * Send a message to all the connectiosn in this topic
      * @param string
      */
    public function broadcast($msg) {
        foreach ($thisi->subscribers as $client) {
            $client->event($this->id, $msg);
        }
    }

    /**
     * @param WampConnection
     */
    public function add(WampConnection $conn) {
        $this->subscribers->attach($conn);
    }

    /**
     * @param WampConnection
     */
    public function remove(WampConnection $conn) {
        if ($this->subscribers->contains($conn)) {
            $this->subscribers->detach($conn);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator() {
        return $this->subscribers;
    }

    /**
     * {@inheritdoc}
     */
    public function count() {
        return $this->subscribers->count();
    }
}