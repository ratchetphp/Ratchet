<?php

namespace Ratchet\Wamp;

use Ratchet\AbstractConnectionDecorator;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\ServerProtocol as WAMP;

/**
 * A ConnectionInterface object wrapper that is passed to your WAMP application
 * representing a client. Methods on this Connection are therefore different.
 *
 * @property \stdClass $WAMP
 */
class WampConnection extends AbstractConnectionDecorator
{
    /**
     * {@inheritdoc}
     */
    public function __construct(ConnectionInterface $connection)
    {
        parent::__construct($connection);

        $this->WAMP = new \StdClass;
        $this->WAMP->sessionId = str_replace('.', '', uniqid(mt_rand(), true));
        $this->WAMP->prefixes = [];

        $this->send(json_encode([WAMP::MSG_WELCOME, $this->WAMP->sessionId, 1, 'Ratchet/0.4.4']));
    }

    /**
     * Successfully respond to a call made by the client
     *
     * @param  string  $id   The unique ID given by the client to respond to
     * @param  array  $data an object or array
     */
    public function callResult(string $id, array $data = []): WampConnection
    {
        return $this->send(json_encode([WAMP::MSG_CALL_RESULT, $id, $data]));
    }

    /**
     * Respond with an error to a client call
     *
     * @param  string  $id The   unique ID given by the client to respond to
     * @param  Topic|string  $errorUri The URI given to identify the specific error
     * @param  string  $desc     A developer-oriented description of the error
     * @param  string  $details An optional human readable detail message to send back
     */
    public function callError(
        string $id,
        string|Topic $errorUri,
        string $desc = '',
        ?string $details = null,
    ): WampConnection {
        if ($errorUri instanceof Topic) {
            $errorUri = (string) $errorUri;
        }

        $data = [WAMP::MSG_CALL_ERROR, $id, $errorUri, $desc];

        if ($details !== null) {
            $data[] = $details;
        }

        return $this->send(json_encode($data));
    }

    /**
     * @param  string  $topic The topic to broadcast to
     * @param  mixed  $message   Data to send with the event.  Anything that is json'able
     */
    public function event(string $topic, mixed $message): WampConnection
    {
        return $this->send(json_encode([WAMP::MSG_EVENT, $topic, $message]));
    }

    public function prefix(string $curie, string $uri): WampConnection
    {
        $this->WAMP->prefixes[$curie] = (string) $uri;

        return $this->send(json_encode([WAMP::MSG_PREFIX, $curie, (string) $uri]));
    }

    /**
     * Get the full request URI from the connection object if a prefix has been established for it
     */
    public function getUri(string $uri): string
    {
        $curieSeparator = ':';

        if (preg_match('/http(s*)\:\/\//', $uri) == false) {
            if (strpos($uri, $curieSeparator) !== false) {
                [$prefix, $action] = explode($curieSeparator, $uri);

                if (isset($this->WAMP->prefixes[$prefix]) === true) {
                    return $this->WAMP->prefixes[$prefix].'#'.$action;
                }
            }
        }

        return $uri;
    }

    /**
     * @internal
     */
    public function send(string $data): ConnectionInterface
    {
        $this->getConnection()->send($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function close($opt = null)
    {
        $this->getConnection()->close($opt);
    }
}
