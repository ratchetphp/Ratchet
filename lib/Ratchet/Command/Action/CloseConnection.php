<?php
namespace Ratchet\Command\Action;
use Ratchet\Command\ActionTemplate;
use Ratchet\SocketObserver;

/**
 * Close the connection to the sockets passed in the constructor
 */
class CloseConnection extends ActionTemplate {
    function execute(SocketObserver $scope = null) {
        $ret = $scope->onClose($this->getSocket());
        $this->getSocket()->close();

        return $ret;
    }
}