<?php
namespace Ratchet\Application\WebSocket\Command\Action;
use Ratchet\SocketInterface;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\ObserverInterface;

class Disconnect extends SendMessage {
    protected $_code = 1000;

    public function setStatusCode($code) {
        $this->_code = (int)$code;

        // re-do message based on code
    }

    public function execute(ObserverInterface $scope = null) {
        parent::execute();
        $this->_socket->close();
    }
}