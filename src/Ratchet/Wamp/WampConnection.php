<?php

namespace Ratchet\Wamp;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\ServerProtocol as WAMP;

/**
 * A ConnectionInterface object wrapper that is passed to your WAMP application
 * representing a client. Methods on this Connection are therefore different.
 * @property \stdClass $WAMP
 */
class WampConnection extends AbstractConnectionDecorator {
    public function __construct(ConnectionInterface $conn) {
        parent::__construct($conn);

        $this->WAMP = new \StdClass;
        $this->WAMP->sessionId = str_replace('.', '', uniqid(mt_rand(), true));
        $this->WAMP->prefixes = [];

        $this->send(json_encode([WAMP::MSG_WELCOME, $this->WAMP->sessionId, 1, \Ratchet\VERSION]));
    }

    /**
     * Get the full request URI from the connection object if a prefix has been established for it
     * @param string $uri
     * @return string
     */
    public function getUri($uri) {
        $curieSeperator = ':';

        if (preg_match('/http(s*)\:\/\//', $uri) == false) {
            if (str_contains($uri, $curieSeperator)) {
                [$prefix, $action] = explode($curieSeperator, $uri);

                if(isset($this->WAMP->prefixes[$prefix]) === true){
                  return $this->WAMP->prefixes[$prefix] . '#' . $action;
                }
            }
        }

        return $uri;
    }

    /**
     * @internal
     *
     * @return static
     */
    #[\Override]
    public function send(string|false $data) {
        $this->getConnection()->send($data);

        return $this;
    }

    #[\Override]
    public function close($opt = null) {
        $this->getConnection()->close($opt);
    }
}
