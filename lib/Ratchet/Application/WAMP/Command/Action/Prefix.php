<?php
namespace Ratchet\Application\WAMP\Command\Action;
use Ratchet\Resource\Command\Action\SendMessage;

/**
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

        return $this->setMessage(json_encode(array(1, $curie, $uri)));
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
