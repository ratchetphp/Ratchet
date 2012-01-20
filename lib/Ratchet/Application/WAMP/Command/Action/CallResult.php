<?php
namespace Ratchet\Application\WAMP\Command\Action;
use Ratchet\Resource\Command\Action\SendMessage;

/**
 */
class CallResult extends SendMessage {
    protected $_id;

    protected $_data;

    public function setResult($callId, array $data = array()) {
        $this->_id   = $callId;
        $this->_data = $data;

        return $this->setMessage(json_encode(array(3, $callId, $data)));
    }

    public function getId() {
        return $this->_id;
    }

    public function getData() {
        return $this->_data;
    }
}