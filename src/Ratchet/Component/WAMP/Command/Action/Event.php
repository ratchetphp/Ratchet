<?php
namespace Ratchet\Component\WAMP\Command\Action;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Component\WAMP\App as WAMP;

/**
 * This is an event in the context of a topicURI
 * This event (message) is to be sent to all subscribers of $uri
 */
class Event extends SendMessage {
    /**
     * @param ...
     * @param string
     * @return Event
     */
    public function setEvent($uri, $msg) {
        return $this->setMessage(json_encode(array(WAMP::MSG_EVENT, $uri, (string)$msg)));
    }
}