<?php
namespace Ratchet\Wamp;
use Ratchet\ConnectionDecorator;
use Ratchet\ConnectionInterface;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\Wamp\ServerProtocol as WAMP;

/**
 * A ConnectionInterface object wrapper that is passed to your WAMP application
 * representing a client. Methods on this Connection are therefore different.
 */
class WampConnection extends AbstractConnectionDecorator {
    use ConnectionDecorator {
        ConnectionDecorator::__construct as _decorator;
    }

    /**
     * {@inheritdoc}
     */
    public function __construct(ConnectionInterface $conn) {
        parent::__construct($conn);

        $this->_decorator($conn, [
            'WAMP.sessionId'     => str_replace('.', '', uniqid(mt_rand(), true)),
            'WAMP.subscriptions' => new \SplObjectStorage,
            'WAMP.prefixes'      => new \ArrayObject
        ]);

        // @deprecated
        $this->WAMP = new \StdClass;
        $this->WAMP->sessionId = $this->get('WAMP.sessionId');
        $this->WAMP->prefixes = $this->get('WAMP.prefixes');

        $this->send(json_encode([WAMP::MSG_WELCOME, $this->get('WAMP.sessionId'), 1, \Ratchet\VERSION]));
    }

    /**
     * Successfully respond to a call made by the client
     * @param string $id   The unique ID given by the client to respond to
     * @param array $data an object or array
     * @return WampConnection
     */
    public function callResult($id, array $data = []) {
        return $this->send(json_encode([WAMP::MSG_CALL_RESULT, $id, $data]));
    }

    /**
     * Respond with an error to a client call
     * @param string $id The   unique ID given by the client to respond to
     * @param string $errorUri The URI given to identify the specific error
     * @param string $desc     A developer-oriented description of the error
     * @param string $details An optional human readable detail message to send back
     * @return WampConnection
     */
    public function callError($id, $errorUri, $desc = '', $details = null) {
        if ($errorUri instanceof Topic) {
            $errorUri = (string)$errorUri;
        }

        $data = [WAMP::MSG_CALL_ERROR, $id, $errorUri, $desc];

        if (null !== $details) {
            $data[] = $details;
        }

        return $this->send(json_encode($data));
    }

    /**
     * @param string $topic The topic to broadcast to
     * @param mixed  $msg   Data to send with the event.  Anything that is json'able
     * @return WampConnection
     */
    public function event($topic, $msg) {
        return $this->send(json_encode([WAMP::MSG_EVENT, (string)$topic, $msg]));
    }

    /**
     * @param string $curie
     * @param string $uri
     * @return WampConnection
     */
    public function prefix($curie, $uri) {
//        $this->get('WAMP.prefixes')[$curie] = (string)$uri;
        $this->properties['WAMP.prefixes'][$curie] = (string)$uri;

        return $this->send(json_encode([WAMP::MSG_PREFIX, $curie, (string)$uri]));
    }

    /**
     * Get the full request URI from the connection object if a prefix has been established for it
     * @param string $uri
     * @return string
     */
    public function getUri($uri) {
        $curieSeperator = ':';

        if (0 === preg_match('/http(s*)\:\/\//', $uri) && strpos($uri, $curieSeperator) !== false) {
            list($prefix, $action) = explode($curieSeperator, $uri);

            if(isset($this->get('WAMP.prefixes')[$prefix]) === true){
              return $this->get('WAMP.prefixes')[$prefix] . '#' . $action;
            }
        }

        return $uri;
    }

    /**
     * @internal
     */
    public function send($data) {
        $this->connection->send($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function close($opt = null) {
        $this->connection->close($opt);
    }
}
