<?php
namespace Ratchet\Application\WAMP\Command\Action;
use Ratchet\Resource\Command\Action\SendMessage;

/**
 * This is an event in the context of a topicURI
 * This event (message) is to be sent to all subscribers of $uri
 */
class Event extends SendMessage {
    /**
     * @param ...
     * @param string
     */
    public function setEvent($uri, $msg) {
        return $this->setMessage(json_encode(array(8, $uri, (string)$msg)));
    }
}