<?php
namespace Ratchet\Component\WAMP\Command\Action;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Component\WAMP\WAMPServerComponent as WAMP;

/**
 * Respond to a client RPC with an error
 */
class CallError extends SendMessage {
    /**
     * @var string
     */
    protected $_id;

    /**
     * @var string
     */
    protected $_uri;

    /**
     * @var string
     */
    protected $_desc = '';

    /**
     * @var string
     */
    protected $_details;

    /**
     * @param string The unique ID given by the client to respond to
     * @param string The URI given by the client ot respond to
     * @param string A developer-oriented description of the error
     * @param string|null An optional human readable detail message to send back
     * @return CallError
     */
    public function setError($callId, $uri, $desc = '', $details = null) {
        $this->_id   = $callId;
        $this->_uri  = $uri;
        $this->_desc = $desc;

        $data = array(WAMP::MSG_CALL_ERROR, $callId, $uri, $desc);

        if (null !== $details) {
            $data[] = $details;
            $this->_details = $details;
        }

        return $this->setMessage(json_encode($data));
    }

    /**
     * @return string|null
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * @return string|null
     */
    public function getUri() {
        return $this->_uri;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->_desc;
    }

    /**
     * @return string|null
     */
    public function getDetails() {
        return $this->_details;
    }
}