<?php

namespace Ratchet\Wamp;
use Ratchet\ConnectionInterface;

/**
 * A topic/channel containing connections that have subscribed to it
 */
class Topic implements \IteratorAggregate, \Countable, \Stringable {
    private readonly \SplObjectStorage $subscribers;

    /**
     * @param string $id Unique ID for this object
     */
    public function __construct(
        private $id
    ) {
        $this->subscribers = new \SplObjectStorage;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    #[\Override]
    public function __toString(): string {
        return $this->getId();
    }

    /**
     * @param WampConnection $conn
     */
    public function add(ConnectionInterface $conn): static {
        $this->subscribers->attach($conn);

        return $this;
    }

    /**
     * @param WampConnection $conn
     */
    public function remove(ConnectionInterface $conn): static {
        if ($this->subscribers->contains($conn)) {
            $this->subscribers->detach($conn);
        }

        return $this;
    }

    /**
     * @return \SplObjectStorage
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function getIterator() {
        return $this->subscribers;
    }

    #[\ReturnTypeWillChange]
    #[\Override]
    public function count() {
        return $this->subscribers->count();
    }
}
