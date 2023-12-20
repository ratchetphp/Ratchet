<?php

namespace Ratchet\Wamp;

use Ratchet\ConnectionInterface;

/**
 * A topic/channel containing connections that have subscribed to it
 */
class Topic implements \Countable, \IteratorAggregate
{
    private \SplObjectStorage $subscribers;

    /**
     * @param  string  $id Unique ID for this object
     */
    public function __construct(
        private string $id
    ) {
        $this->subscribers = new \SplObjectStorage;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->getId();
    }

    /**
     * Send a message to all the connections in this topic
     *
     * @param  string|array  $message Payload to publish
     * @param  array  $exclude A list of session IDs the message should be excluded from (blacklist)
     * @param  array  $eligible A list of session Ids the message should be send to (whitelist)
     * @return Topic The same Topic object to chain
     */
    public function broadcast(string|array $message, array $exclude = [], array $eligible = []): self
    {
        $useEligible = (bool) count($eligible);
        foreach ($this->subscribers as $client) {
            if (in_array($client->WAMP->sessionId, $exclude)) {
                continue;
            }

            if ($useEligible && ! in_array($client->WAMP->sessionId, $eligible)) {
                continue;
            }

            $client->event($this->id, $message);
        }

        return $this;
    }

    /**
     * @param  WampConnection  $connection
     */
    public function has(ConnectionInterface $connection): bool
    {
        return $this->subscribers->contains($connection);
    }

    /**
     * @param  WampConnection  $connection
     */
    public function add(ConnectionInterface $connection): self
    {
        $this->subscribers->attach($connection);

        return $this;
    }

    /**
     * @param  WampConnection  $connection
     */
    public function remove(ConnectionInterface $connection): self
    {
        if ($this->subscribers->contains($connection)) {
            $this->subscribers->detach($connection);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return $this->subscribers;
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return $this->subscribers->count();
    }
}
