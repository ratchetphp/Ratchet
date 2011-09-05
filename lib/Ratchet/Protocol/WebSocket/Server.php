<?php
namespace Ratchet\Protocol\WebSocket;
use Ratchet\ServerInterface;
use Ratchet\Protocol\ProtocolInterface;

class Server implements ProtocolInterface {
    protected $_server = null;

    public function __construct(ServerInterface $server) {
        $this->_server = $server;
    }

    public function run() {
    }
}