<?php
namespace Ratchet;
use Ratchet\Server\Aggregator;
use Ratchet\Protocol\ProtocolInterface;
use Ratchet\Logging\LoggerInterface;
use Ratchet\Logging\NullLogger;

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

    /**
     * @var array of Socket Resources
     */
    protected $_resources   = array();

    /**
     * @var ArrayIterator of Resouces (Socket type) as keys, Ratchet\Socket as values
     */
    protected $_connections;

    /**
     * @type Logging\LoggerInterface;
     */
    protected $_log;

    /**
     * @param Ratchet\Socket
     * @param boolean True, enables debug mode and the server doesn't infiniate loop
     * @param Logging\LoggerInterface
     */
    public function __construct(Socket $host, LoggerInterface $logger = null) {
        $this->_master = $host;
        $socket = $host->getResource();
        $this->_resources[] = $socket;

        if (null === $logger) {
            $logger = new NullLogger;
        }
        $this->_log = $logger;

        $this->_connections = new \ArrayIterator(array());
    }

    /**
     * @param Logging\LoggerInterface
     */
    public function setLogger(LoggerInterface $logger) {
        $this->_log = $logger;
    }

    /**
     * @todo Receive an interface that creates clients based on interface, decorator pattern for Socket
     */
    public function setClientFactory($s) {
    }

    /**
     * @param ReceiverInterface
     * @return Server
     */
    public function attatchReceiver(ReceiverInterface $receiver) {
        $receiver->setUp($this);
        $this->_receivers[spl_object_hash($receiver)] = $receiver;

        return $this;
    }

    /**
     * @return Socket
     */
    public function getMaster() {
        return $this->_master;
    }

    /**
     * @return ArrayIterator of Sockets
     */
    public function getIterator() {
        return $this->_connections;
    }

    /*
     * @param mixed
     * @param int
     * @throws Exception
     * @todo Validate address.  Use socket_get_option, if AF_INET must be IP, if AF_UNIX must be path
     * @todo Should I make handling open/close/msg an application?
     */
    public function run($address = '127.0.0.1', $port = 1025) {
        if (count($this->_receivers) == 0) {
            throw new \RuntimeException("No receiver has been attached to the server");
        }

        set_time_limit(0);
        ob_implicit_flush();

        if (false === ($this->_master->bind($address, (int)$port))) {
            throw new Exception;
        }

        if (false === ($this->_master->listen())) {
            throw new Exception;
        }

        do {
            try {
                $changed     = $this->_resources;
                $num_changed = $this->_master->select($changed, $write = null, $except = null, null);

    			foreach($changed as $resource) {
                    if ($this->_master->getResource() === $resource) {
                        $this->onConnect($this->_master);
                    } else {
                        $conn  = $this->_connections[$resource];
                        $data  = null;
                        $bytes = $conn->recv($data, 4096, 0);

                        if ($bytes == 0) {
                            $this->onClose($conn);
                        } else {
                            $this->onMessage($data, $conn);

                        // new Message
                        // $this->_receivers->handleMessage($msg, $conn);
                        }
                    }
                }
            } catch (Exception $e) {
                $this->_log->error($e->getMessage());
            }
        } while (true);

//        $this->_master->set_nonblock();
//        declare(ticks = 1); 
    }

    protected function onConnect(Socket $master) {
        $new_connection     = clone $master;
        $this->_resources[] = $new_connection->getResource();
        $this->_connections[$new_connection->getResource()] = $new_connection;

        $this->_log->note('New connection, ' . count($this->_connections) . ' total');

        // /here $this->_receivers->handleConnection($new_connection);
        $this->tmpRIterator('handleConnect', $new_connection);
    }

    protected function onMessage($msg, Socket $from) {
        $this->_log->note('New message "' . trim($msg) . '"');
        $this->tmpRIterator('handleMessage', $msg, $from);
    }

    protected function onClose(Socket $conn) {
        $resource = $conn->getResource();
        $this->tmpRIterator('handleClose', $conn);
        // $this->_receivers->handleDisconnect($conn);

        unset($this->_connections[$resource]);
        unset($this->_resources[array_search($resource, $this->_resources)]);

        $this->_log->note('Connection closed, ' . count($this->_connections) . ' connections remain (' . count($this->_resources) . ')');
    }

    /**
     * @todo Remove this method, make the receivers container implement the composite pattern
     */
    protected function tmpRIterator() {
        $args = func_get_args();
        $fn   = array_shift($args);
        foreach ($this->_receivers as $app) {
            call_user_func_array(array($app, $fn), $args);
        }
    }
}