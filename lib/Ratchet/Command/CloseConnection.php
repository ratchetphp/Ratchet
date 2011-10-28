<?php
namespace Ratchet\Command;
use Ratchet\SocketCollection;

/**
 * Close the connection to the sockets passed in the constructor
 */
class CloseConnection implements CommandInterface {
    /**
     * @var SocketCollection
     */
    protected $_sockets;

    public function __construct(SocketCollection $sockets) {
        $this->_sockets = $sockets;
    }

    function execute() {
        foreach ($this->_sockets as $socket) {
            $socket->close();
        }
    }
}