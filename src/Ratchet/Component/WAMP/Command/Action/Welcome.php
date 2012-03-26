<?php
namespace Ratchet\Component\WAMP\Command\Action;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Component\WAMP\WAMPServerComponent as WAMP;

/**
 * @todo Needs work - sessionId needs to be stored w/ the Connection object
 */
class CallResult extends SendMessage {
    public function setWelcome($sessionId, $serverIdent = '') {
        return $this->setMessage(json_encode(array(WAMP::MSG_WELCOME, $sessionId, 1, $serverIdent)));
    }
}