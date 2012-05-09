<?php
namespace Ratchet\Wamp;
use Ratchet\ComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * A (not literal) extension of Ratchet\ConnectionInterface
 * onMessage is replaced by various types of messages for this protocol (pub/sub or rpc)
 * @todo Thought: URI as class.  Class has short and long version stored (if as prefix)
 */
interface WampServerInterface extends ComponentInterface {
    /**
     * An RPC call has been received
     * @param Ratchet\Connection
     * @param string
     * @param ...
     * @param array Call parameters received from the client
     */
    function onCall(ConnectionInterface $conn, $id, $procURI, array $params);

    /**
     * A request to subscribe to a URI has been made
     * @param Ratchet\Connection
     * @param ...
     */
    function onSubscribe(ConnectionInterface $conn, $uri);

    /**
     * A request to unsubscribe from a URI has been made
     * @param Ratchet\Connection
     * @param ...
     */
    function onUnSubscribe(ConnectionInterface $conn, $uri);

    /**
     * A client is attempting to publish content to a subscribed connections on a URI
     * @param Ratchet\Connection
     * @param ...
     * @param string
     */
    function onPublish(ConnectionInterface $conn, $uri, $event);
}