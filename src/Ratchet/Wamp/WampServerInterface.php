<?php
namespace Ratchet\Wamp;
use Ratchet\ComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * An extension of Ratchet\ComponentInterface to server a WAMP application
 * onMessage is replaced by various types of messages for this protocol (pub/sub or rpc)
 */
interface WampServerInterface extends ComponentInterface {
    /**
     * An RPC call has been received
     * @param Ratchet\ConnectionInterface
     * @param string The unique ID of the RPC, required to respond to
     * @param string|Topic The topic to execute the call against
     * @param array Call parameters received from the client
     */
    function onCall(ConnectionInterface $conn, $id, $topic, array $params);

    /**
     * A request to subscribe to a topic has been made
     * @param Ratchet\ConnectionInterface
     * @param string|Topic The topic to subscribe to
     */
    function onSubscribe(ConnectionInterface $conn, $topic);

    /**
     * A request to unsubscribe from a topic has been made
     * @param Ratchet\ConnectionInterface
     * @param string|Topic The topic to unsubscribe from
     */
    function onUnSubscribe(ConnectionInterface $conn, $topic);

    /**
     * A client is attempting to publish content to a subscribed connections on a URI
     * @param Ratchet\ConnectionInterface
     * @param string|Topic The topic the user has attempted to publish to
     * @param string Payload of the publish
     * @param array A list of session IDs the message should be excluded from (blacklist)
     * @param array A list of session Ids the message should be send to (whitelist)
     */
    function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible);
}