<?php
namespace Ratchet\Application\WebSocket\Command\Action;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Application\ApplicationInterface;

class Disconnect extends SendMessage {
    protected $_code = 1000;

    public function setStatusCode($code) {
        $this->_code = (int)$code;

        // re-do message based on code
    }

    public function execute(ApplicationInterface $scope = null) {
        parent::execute();
        $this->_socket->close();
    }
}