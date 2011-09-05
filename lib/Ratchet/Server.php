<?php
namespace Ratchet;
use Ratchet\Protocol\ProtocolInterface;

class Server implements ServerInterface {
    protected $_master = null;
    protected $_app    = null;
    protected $_debug  = false;

    protected $_connections = Array();

    /**
     * @param Ratchet\Socket
     * @param boolean True, enables debug mode and the server doesn't infiniate loop
     */
    public function __construct(Socket $socket, $debug = false) {
        $this->_master = $socket;
        $this->_debug  = (boolean)$debug;
    }

    public function attatchApplication(ApplicationInterface $app) {
        $this->_app = $app;
    }

    /*
     * @param mixed
     * @param int
     * @throws Ratchet\Exception
     */
    public function run($address = '127.0.0.1', $port = 1025) {
        if (!($this->_app instanceof ApplicationInterface)) {
            throw new \RuntimeException("No application has been bound to the server");
        }

        set_time_limit(0);
        ob_implicit_flush();

        if (false === ($this->_master->bind($address, (int)$port))) { // perhaps I should do some checks here...
            throw new Exception();
        }

        if (false === ($this->_master->listen())) {
            throw new Exception();
        }

        do {
			$changed     = $this->_connections;
			$num_changed = socket_select($changed_sockets, $write = NULL, $except = NULL, NULL);
//			foreach($changed as $) 

        } while (!$this->_debug);

//        $this->_master->set_nonblock();
//        declare(ticks = 1); 
    }
}