<?php
namespace Ratchet;
use Ratchet\Protocol\ProtocolInterface;

class Server implements ServerInterface {
    protected $_master = null;
    protected $_app    = null;

    public function __construct(Socket $socket) {
        $this->_master = $socket;
    }

    public function attatchApplication(ApplicationInterface $app) {
        $this->_app = $app;
    }

    public function run() {
        if (!($this->_app instanceof ApplicationInterface)) {
            throw new \RuntimeException("No application has been bound to the server");
        }

        set_time_limit(0);
        ob_implicit_flush();

        do {
            
        } while (true);
//        $this->_master->set_nonblock();
//        declare(ticks = 1); 
    }
}