<?php
namespace Ratchet\Component\WAMP\Resource;
use Ratchet\Resource\AbstractConnectionDecorator;
use Ratchet\Component\WAMP\WAMPServerComponent as WAMP;

/**
 * @property stdClass $WAMP
 */
class Connection extends AbstractConnectionDecorator {
    public function __construct() {
        $this->WAMP            = new \StdClass;
        $this->WAMP->sessionId = uniqid();
        $this->WAMP->prefixes  = array();

        $this->getConnection()->send(json_encode(array(WAMP::MSG_WELCOME, $this->WAMP->sessionId, 1, \Ratchet\Resource\VERSION)));
    }

    public function callResponse() {
    }

    public function callError() {
    }

    public function event() {
    }

    public function addPrefix($curie, $uri) {
    }

    public function send($data) {
        $this->getConnection()->send($data);
    }

    public function close() {
        $this->getConnection()->close();
    }
}