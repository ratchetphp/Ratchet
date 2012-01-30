<?php
namespace Ratchet\Application\WAMP\Command\Action;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Application\WAMP\App as WAMP;

class CallError extends SendMessage {
    protected $_id;

    protected $_uri;

    protected $_desc;

    public function setError($callId, $uri, $desc) {
        $this->_id   = $callId;
        $this->_uri  = $uri;
        $this->_desc = $desc;

        return $this->setMessage(json_encode(array(WAMP::MSG_CALL_ERROR, $callId, $uri, $desc)));
    }

    public function getId() {
        return $this->_id;
    }

    public function getUri() {
        return $this->_uri;
    }

    public function getDescription() {
        return $this->_desc;
    }
}