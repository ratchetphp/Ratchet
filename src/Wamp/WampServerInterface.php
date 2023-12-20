<?php

namespace Ratchet\Wamp;

use Ratchet\ComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * An extension of Ratchet\ComponentInterface to server a WAMP application
 * onMessage is replaced by various types of messages for this protocol (pub/sub or rpc)
 */
interface WampServerInterface extends ComponentInterface
{
    /**
     * An RPC call has been received
     *
     * @param  string  $id The unique ID of the RPC, required to respond to
     * @param  string|Topic  $topic The topic to execute the call against
     * @param  array  $params Call parameters received from the client
     */
    public function onCall(ConnectionInterface $connection, string $id, string|Topic $topic, array $params);

    /**
     * A request to subscribe to a topic has been made
     *
     * @param  string|Topic  $topic The topic to subscribe to
     */
    public function onSubscribe(ConnectionInterface $connection, string|Topic $topic);

    /**
     * A request to unsubscribe from a topic has been made
     *
     * @param  string|Topic  $topic The topic to unsubscribe from
     */
    public function onUnSubscribe(ConnectionInterface $connection, string|Topic $topic);

    /**
     * A client is attempting to publish content to a subscribed connections on a URI
     *
     * @param  string|Topic  $topic The topic the user has attempted to publish to
     * @param  string  $event Payload of the publish
     * @param  array  $exclude A list of session IDs the message should be excluded from (blacklist)
     * @param  array  $eligible A list of session Ids the message should be send to (whitelist)
     */
    public function onPublish(
        ConnectionInterface $connection,
        string|Topic $topic,
        string $event,
        array $exclude,
        array $eligible,
    );
}
