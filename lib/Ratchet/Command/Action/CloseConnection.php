<?php
namespace Ratchet\Command\Action;
use Ratchet\Command\CommandInterface;
use Ratchet\SocketInterface;

/**
 * Close the connection to the sockets passed in the constructor
 */
class CloseConnection implements CommandInterface {
    /**
     * @var SocketInterface
     */
    protected $_socket;

    public function __construct(SocketInterface $socket) {
        $this->_socket = $socket;
    }

    function execute() {
        $this->_socket->close();
    }
}