<?php
namespace Ratchet;
use Ratchet\Protocol\ProtocolInterface;

class Server implements ServerInterface {
    protected $master = null;

    public function __construct(Socket $socket) {
        $this->_master = $socket;
    }

    public function run() {
        set_time_limit(0);
        ob_implicit_flush();
//        $this->_master->set_nonblock();
//        declare(ticks = 1); 
    }
}