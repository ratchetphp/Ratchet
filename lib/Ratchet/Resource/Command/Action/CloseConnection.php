<?php
namespace Ratchet\Resource\Command\Action;
use Ratchet\Resource\Command\ActionTemplate;
use Ratchet\ObserverInterface;
use Ratchet\SocketInterface;
use Ratchet\Resource\Command\CommandInterface;
use Ratchet\Resource\Command\Composite;

/**
 * Close the connection to the sockets passed in the constructor
 */
class CloseConnection extends ActionTemplate {
    function execute(ObserverInterface $scope = null) {
        // All this code allows an application to have its onClose method executed before the socket is actually closed
        $ret = $scope->onClose($this->getSocket());

        if ($ret instanceof CommandInterface) {
            $comp = new Composite;
            $comp->enqueue($ret);

            $rt = new Runtime($this->getSocket());
            $rt->setCommand(function(SocketInterface $socket, ObserverInterface $scope) {
                $socket->close();
            });
            $comp->enqueue($rt);

            return $comp;
        }

        $this->getSocket()->close();
    }
}