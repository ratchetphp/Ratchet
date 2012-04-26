<?php
namespace Ratchet\Component\WAMP\Command\Action;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Component\WAMP\WAMPServerComponent as WAMP;

/**
 * Send Welcome message to each new connecting client
 */
class Welcome extends SendMessage {
    /**
     * @param string The unique identifier to mark the client
     * @param string The server application name/version
     * @return Welcome
     */
    public function setWelcome($sessionId, $serverIdent = '') {
        return $this->setMessage(json_encode(array(WAMP::MSG_WELCOME, $sessionId, 1, $serverIdent)));
    }
}