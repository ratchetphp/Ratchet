<?php
namespace Ratchet\Component\WAMP\Command\Action;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Component\WAMP\WAMPServerComponent as WAMP;

/**
 */
class CallResult extends SendMessage {
    protected $_id;

    protected $_data;

    public function setResult($callId, array $data = array()) {
        $this->_id   = $callId;
        $this->_data = $data;

        return $this->setMessage(json_encode(array(WAMP::MSG_CALL_RESULT, $callId, $data)));
    }

    public function getId() {
        return $this->_id;
    }

    public function getData() {
        return $this->_data;
    }
}