<?php

namespace Ratchet\Wamp;

use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServerInterface;

class TopicManager implements WampServerInterface, WsServerInterface
{
    protected array $topicLookup = [];

    public function __construct(protected WampServerInterface $app)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection)
    {
        $connection->WAMP->subscriptions = new \SplObjectStorage;
        $this->app->onOpen($connection);
    }

    /**
     * {@inheritdoc}
     */
    public function onCall(ConnectionInterface $connection, $id, $topic, array $params)
    {
        $this->app->onCall($connection, $id, $this->getTopic($topic), $params);
    }

    /**
     * {@inheritdoc}
     */
    public function onSubscribe(ConnectionInterface $connection, $topic)
    {
        $topicObj = $this->getTopic($topic);

        if ($connection->WAMP->subscriptions->contains($topicObj)) {
            return;
        }

        $this->topicLookup[$topic]->add($connection);
        $connection->WAMP->subscriptions->attach($topicObj);
        $this->app->onSubscribe($connection, $topicObj);
    }

    /**
     * {@inheritdoc}
     */
    public function onUnsubscribe(ConnectionInterface $connection, $topic)
    {
        $topicObj = $this->getTopic($topic);

        if (! $connection->WAMP->subscriptions->contains($topicObj)) {
            return;
        }

        $this->cleanTopic($topicObj, $connection);

        $this->app->onUnsubscribe($connection, $topicObj);
    }

    /**
     * {@inheritdoc}
     */
    public function onPublish(
        ConnectionInterface $connection,
        string|Topic $topic,
        string $event,
        array $exclude,
        array $eligible,
    ) {
        $this->app->onPublish($connection, $this->getTopic($topic), $event, $exclude, $eligible);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        $this->app->onClose($connection);

        foreach ($this->topicLookup as $topic) {
            $this->cleanTopic($topic, $connection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $exception)
    {
        $this->app->onError($connection, $exception);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubProtocols(): array
    {
        if ($this->app instanceof WsServerInterface) {
            return $this->app->getSubProtocols();
        }

        return [];
    }

    protected function getTopic(string $topic): Topic
    {
        if (! array_key_exists($topic, $this->topicLookup)) {
            $this->topicLookup[$topic] = new Topic($topic);
        }

        return $this->topicLookup[$topic];
    }

    protected function cleanTopic(Topic $topic, ConnectionInterface $connection)
    {
        if ($connection->WAMP->subscriptions->contains($topic)) {
            $connection->WAMP->subscriptions->detach($topic);
        }

        $this->topicLookup[$topic->getId()]->remove($connection);

        if ($topic->count() === 0) {
            unset($this->topicLookup[$topic->getId()]);
        }
    }
}
