<?php
namespace Ratchet\Command\Action;
use Ratchet\Command\ActionInterface;
use Ratchet\SocketInterface;
use Ratchet\SocketObserver;

/**
 * Close the connection to the sockets passed in the constructor
 * @todo The server does not seem to be notified when a resource is closed by this class...
 */
class CloseConnection implements ActionInterface {
    /**
     * @var SocketInterface
     */
    protected $_socket;

    public function __construct(SocketInterface $socket) {
        $this->_socket = $socket;
    }

    function execute(SocketObserver $scope = null) {
        $ret = $scope->onClose($this->_socket);
        $this->_socket->close();

        return $ret;
    }
}