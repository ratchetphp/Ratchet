<?php
namespace Ratchet\Server;
use Ratchet\Socket;

class Client {
    protected $_socket;

    public function __construct(Socket $socket) {
        $this->_socket = $socket;
    }

    
}