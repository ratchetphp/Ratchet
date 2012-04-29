<?php
namespace Ratchet\Component\WAMP\Command\Action;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Component\WAMP\WAMPServerComponent as WAMP;

/**
 * This is an event in the context of a topicURI
 * This event (message) is to be sent to all subscribers of $uri
 */
class Event extends SendMessage {
    /**
     * @param string The URI or CURIE to broadcast to
     * @param mixed Data to send with the event.  Anything that is json'able
     * @return Event
     */
    public function setEvent($uri, $msg) {
        return $this->setMessage(json_encode(array(WAMP::MSG_EVENT, $uri, $msg)));
    }
}