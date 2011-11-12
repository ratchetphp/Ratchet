<?php
namespace Ratchet\Protocol\WebSocket\Command\Action;
use Ratchet\SocketInterface;
use Ratchet\Command\Action\SendMessage;
use Ratchet\SocketObserver;

class Disconnect extends SendMessage {
    protected $_code = 1000;

    public function setStatusCode($code) {
        $this->_code = (int)$code;

        // re-do message based on code
    }

    public function execute(SocketObserver $scope = null) {
        parent::execute();
        $this->_socket->close();
    }
}