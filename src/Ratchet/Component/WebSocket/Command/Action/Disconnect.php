<?php
namespace Ratchet\Component\WebSocket\Command\Action;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Component\ComponentInterface;

/**
 * Not yet implemented/completed
 */
class Disconnect extends SendMessage {
    protected $_code = 1000;

    public function setStatusCode($code) {
        $this->_code = (int)$code;

        // re-do message based on code
    }

    public function execute(ComponentInterface $scope = null) {
        parent::execute();
        $this->_socket->close();
    }
}