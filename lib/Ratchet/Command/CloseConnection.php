<?php
namespace Ratchet\Command;
use Ratchet\SocketInterface;

/**
 * Close the connection to the sockets passed in the constructor
 */
class CloseConnection implements CommandInterface {
    /**
     * @var SocketInterface
     */
    protected $_socket;

    public function __construct(SocketInterface $sockets) {
        $this->_socket = $sockets;
    }

    function execute() {
        $this->_socket->close();
    }
}