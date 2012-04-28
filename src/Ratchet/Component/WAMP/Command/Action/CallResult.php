<?php
namespace Ratchet\Component\WAMP\Command\Action;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Component\WAMP\WAMPServerComponent as WAMP;

/**
 * Respond to a client RPC
 */
class CallResult extends SendMessage {
    /**
     * @var string
     */
    protected $_id;

    /**
     * @var array
     */
    protected $_data;

    /**
     * @param string The unique ID given by the client to respond to
     * @param array An array of data to return to the client
     * @return CallResult
     */
    public function setResult($callId, array $data = array()) {
        $this->_id   = $callId;
        $this->_data = $data;

        return $this->setMessage(json_encode(array(WAMP::MSG_CALL_RESULT, $callId, $data)));
    }

    /**
     * @return string|null
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * @return array|null
     */
    public function getData() {
        return $this->_data;
    }
}