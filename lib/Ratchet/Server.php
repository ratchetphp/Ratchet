<?php
namespace Ratchet;
use Ratchet\Protocol\ProtocolInterface;

class Server implements ServerInterface {
    protected $master = null;

    public function __construct(Socket $socket) {
        $this->_master = $socket;
    }

    public function run() {
    }
}