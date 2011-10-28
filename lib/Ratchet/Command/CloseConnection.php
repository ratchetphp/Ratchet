<?php
namespace Ratchet\Command;
use Ratchet\SocketCollection;

class CloseConnection implements CommandInterface {
    protected $_sockets;

    public function __construct(SocketCollection $sockets) {
        $this->_sockets = $sockets;
    }

    function execute() {
        $this->_sockets->close();
    }
}