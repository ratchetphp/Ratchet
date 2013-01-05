<?php
namespace Ratchet\Wamp;
use Ratchet\ConnectionInterface;

/**
 * A topic/channel containing connections that have subscribed to it
 */
class Topic implements \IteratorAggregate, \Countable {
    private $id;

    private $subscribers;

    /**
     * @param string $topicId Unique ID for this object
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

    public function __toString() {
        return $this->getId();
    }

    /**
      * Send a message to all the connections in this topic
      * @param string $msg
      * @return Topic
      */
    public function broadcast($msg) {
        foreach ($this->subscribers as $client) {
            $client->event($this->id, $msg);
        }

        return $this;
    }

    /**
     * @param  WampConnection $conn
     * @return boolean
     */
    public function has(ConnectionInterface $conn) {
        return $this->subscribers->contains($conn);
    }

    /**
     * @param WampConnection $conn
     * @return Topic
     */
    public function add(ConnectionInterface $conn) {
        $this->subscribers->attach($conn);

        return $this;
    }

    /**
     * @param WampConnection $conn
     * @return Topic
     */
    public function remove(ConnectionInterface $conn) {
        if ($this->subscribers->contains($conn)) {
            $this->subscribers->detach($conn);
        }

        return $this;
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