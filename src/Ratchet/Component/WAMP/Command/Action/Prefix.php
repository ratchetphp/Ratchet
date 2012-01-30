<?php
namespace Ratchet\Component\WAMP\Command\Action;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Component\WAMP\App as WAMP;

/**
 * Send a curie to uri mapping to the client
 * Both sides will agree to send the curie, representing the uri,
 * resulting in less data transfered
 */
class Prefix extends SendMessage {
    protected $_curie;
    protected $_uri;

    /**
     * @param string
     * @param string
     * @return Prefix
     */
    public function setPrefix($curie, $uri) {
        $this->_curie = $curie;
        $this->_uri   = $uri;

        return $this->setMessage(json_encode(array(WAMP::MSG_PREFIX, $curie, $uri)));
    }

    /**
     * @return string
     */
    public function getCurie() {
        return $this->_curie;
    }

    /**
     * @return string
     */
    public function getUri() {
        return $this->_uri;
    }
}
