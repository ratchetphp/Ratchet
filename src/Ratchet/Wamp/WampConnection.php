<?php

namespace Ratchet\Wamp;
use Override;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\ServerProtocol as WAMP;

/**
 * A ConnectionInterface object wrapper that is passed to your WAMP application
 * representing a client. Methods on this Connection are therefore different.
 * @property \stdClass $WAMP
 */
class WampConnection extends AbstractConnectionDecorator
{
    public function __construct(ConnectionInterface $conn)
    {
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
    public function getUri(string $uri): string
    {
        $curieSeparator = ':';

        if (false === preg_match('/http(s*)\:\/\//', $uri)) {
            if (str_contains($uri, $curieSeparator)) {
                [$prefix, $action] = explode($curieSeparator, $uri);

                if(isset($this->WAMP->prefixes[$prefix]) === true){
                  return $this->WAMP->prefixes[$prefix] . '#' . $action;
                }
            }
        }

        return $uri;
    }

    /**
     * @internal
     */
    #[Override]
    public function send(string $data): ConnectionInterface
    {
        $this->getConnection()->send($data);

        return $this;
    }

    #[Override]
    public function close($opt = null): void
    {
        $this->getConnection()->close($opt);
    }
}
