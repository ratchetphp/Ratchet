<?php
namespace Ratchet\Resource\Command\Action;
use Ratchet\Resource\Command\ActionTemplate;
use Ratchet\Application\ApplicationInterface;
use Ratchet\Resource\Connection;
use Ratchet\Resource\Command\CommandInterface;
use Ratchet\Resource\Command\Composite;

/**
 * Close the connection to the sockets passed in the constructor
 */
class CloseConnection extends ActionTemplate {
    function execute(ApplicationInterface $scope = null) {
        // All this code allows an application to have its onClose method executed before the socket is actually closed
        $ret = $scope->onClose($this->getConnection());

        if ($ret instanceof CommandInterface) {
            $comp = new Composite;
            $comp->enqueue($ret);

            $rt = new Runtime($this->getConnection());
            $rt->setCommand(function(Connection $conn, ApplicationInterface $scope) {
                $conn->getSocket()->close();
            });
            $comp->enqueue($rt);

            return $comp;
        }

        $this->getConnection()->getSocket()->close();
    }
}