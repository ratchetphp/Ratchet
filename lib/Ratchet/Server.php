<?php
namespace Ratchet;
use Ratchet\Server\Aggregator;
use Ratchet\Protocol\ProtocolInterface;

class Server implements ServerInterface {
    /**
     * The master socket, receives all connections
     * @type Socket
     */
    protected $_master = null;

    /**
     * @todo This needs to implement the composite pattern
     * @var array of ReceiverInterface
     */
    protected $_receivers   = array();

    protected $_resources   = array();

    /**
     * @type array of Ratchet\Server\Client
     */
    protected $_connections = array();

    /**
     * @param Ratchet\Socket
     * @param boolean True, enables debug mode and the server doesn't infiniate loop
     */
    public function __construct(Socket $host) {
        $this->_master = $host;

        $socket = $host->getResource();

        $this->_resources[]          = $socket;
        $this->_connections[$socket] = $host;
    }

    /**
     * @todo Receive an interface that creates clients based on interface, decorator pattern for Socket
     */
    public function setClientFactory($s) {
    }

    public function attatchReceiver(ReceiverInterface $receiver) {
        $receiver->setUp($this);
        $this->_receivers[spl_object_hash($receiver)] = $receiver;
    }

    public function getMaster() {
        return $this->_master;
    }

    public function getClients() {
        return $this->_connections;
    }

    /*
     * @param mixed
     * @param int
     * @throws Exception
     * @todo Validate address.  Use socket_get_option, if AF_INET must be IP, if AF_UNIX must be path
     */
    public function run($address = '127.0.0.1', $port = 1025) {
        if (count($this->_receivers) == 0) {
            throw new \RuntimeException("No receiver has been attached to the server");
        }

        set_time_limit(0);
        ob_implicit_flush();

// socket_create_listen($port); instead of create, bind, listen

        if (false === ($this->_master->bind($address, (int)$port))) { // perhaps I should do some checks here...
            throw new Exception;
        }

        if (false === ($this->_master->listen())) {
            throw new Exception;
        }

        do {
			$changed     = $this->_resources;
			$num_changed = @socket_select($changed, $write = NULL, $except = NULL, NULL);
			foreach($changed as $resource) {
                if ($this->_master->getResource() == $resource) {
                    $new_connection     = clone $this->_master;
                    $this->_resources[] = $new_connection->getResource();
                    $this->_connections[$new_connection->getResource()] = $new_connection;

                    // /here $this->_receivers->handleConnection($new_connection);
                    $this->tmpRIterator('handleConnect', $new_connection);
                } else {
                    $conn  = $this->_connections[$resource];
                    $data  = null;
                    $bytes = $conn->recv($data, 4096, 0);

                    if ($bytes == 0) {
                        $this->tmpRIterator('handleClose', $conn);
                        // $this->_receivers->handleDisconnect($conn);

                        unset($this->_connections[$resource]);
                        unset($this->_resources[array_search($resource, $this->_resources)]);
                    } else {
                        $this->tmpRIterator('handleMessage', $data, $conn);
                        // new Message
                        // $this->_receivers->handleMessage($msg, $conn);
                    }
                }
            }
        } while (true);

//        $this->_master->set_nonblock();
//        declare(ticks = 1); 
    }

    protected function tmpRIterator() {
        $args = func_get_args();
        $fn   = array_shift($args);
        foreach ($this->_receivers as $app) {
            call_user_func_array(array($app, $fn), $args);
        }
    }
}