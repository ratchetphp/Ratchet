<?php
namespace Ratchet\Command\Action;
use Ratchet\Command\ActionTemplate;
use Ratchet\SocketObserver;
use Ratchet\SocketInterface;
use Ratchet\Command\CommandInterface;
use Ratchet\Command\Composite;

/**
 * Close the connection to the sockets passed in the constructor
 */
class CloseConnection extends ActionTemplate {
    function execute(SocketObserver $scope = null) {
        $ret = $scope->onClose($this->getSocket());

        if ($ret instanceof CommandInterface) {
            $comp = new Composite;
            $comp->enqueue($ret);

            $rt = new Runtime($this->getSocket());
            $rt->setCommand(function(SocketInterface $socket, SocketObserver $scope) {
                $socket->close();
            });

            $comp->enqueue($rt);

            return $comp;
        }

        $this->getSocket()->close();
    }
}