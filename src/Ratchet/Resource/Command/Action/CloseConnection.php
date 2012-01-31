<?php
namespace Ratchet\Resource\Command\Action;
use Ratchet\Component\ComponentInterface;
use Ratchet\Resource\ConnectionInterface;
use Ratchet\Resource\Command\CommandInterface;
use Ratchet\Resource\Command\Composite;

/**
 * Close the connection to the sockets passed in the constructor
 */
class CloseConnection extends ActionTemplate {
    function execute(ComponentInterface $scope = null) {
        // All this code allows an application to have its onClose method executed before the socket is actually closed
        $ret = $scope->onClose($this->getConnection());

        if ($ret instanceof CommandInterface) {
            $comp = new Composite;
            $comp->enqueue($ret);

            $rt = new Runtime($this->getConnection());
            $rt->setCommand(function(ConnectionInterface $conn, ComponentInterface $scope) {
                $conn->getSocket()->close();
            });
            $comp->enqueue($rt);

            return $comp;
        }

        $this->getConnection()->getSocket()->close();
    }
}