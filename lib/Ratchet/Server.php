<?php
namespace Ratchet;
use Ratchet\Server\Aggregator;
use Ratchet\Protocol\ProtocolInterface;
use Ratchet\Logging\LoggerInterface;
use Ratchet\Logging\NullLogger;

/**
 * Creates an open-ended socket to listen on a port for incomming connections.  Events are delegated through this to attached applications
 * @todo Consider using _connections as master reference and passing iterator_to_array(_connections) to socket_select
 * @todo Move SocketObserver methods to separate class, create, wrap class in __construct
 * @todo Currently passing Socket object down the decorated chain - should be sending reference to it instead; Receivers do not interact with the Socket directly, they do so through the Command pattern
 */
class Server implements SocketObserver, \IteratorAggregate {
    /**
     * The master socket, receives all connections
     * @type Socket
     */
    protected $_master = null;

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
     * @var SocketObserver
     * Maybe temporary?
     */
    protected $_app;

    /**
     * @param Ratchet\Socket
     * @param SocketObserver
     * @param Logging\LoggerInterface
     */
    public function __construct(SocketInterface $host, SocketObserver $application, LoggerInterface $logger = null) {
        $this->_master = $host;
        $socket = $host->getResource();
        $this->_resources[] = $socket;

        if (null === $logger) {
            $logger = new NullLogger;
        }
        $this->_log = $logger;

        $this->_connections = new \ArrayIterator(array());

        $this->_app = $application;
    }

    /**
     * @todo Test this method
     */
    public function newCommand($cmd, SocketCollection $sockets) {
        $class = __NAMESPACE__ . '\\Server\\Command\\' . $cmd;
        if (!class_exists($class)) {
            throw new \UnexpectedValueException("Command {$cmd} not found");
        }

        return new $cmd($sockets);
    }

    /**
     * @param Logging\LoggerInterface
     */
    public function setLogger(LoggerInterface $logger) {
        $this->_log = $logger;
    }

    /**
     * @return ArrayIterator of SocketInterfaces
     */
    public function getIterator() {
        return $this->_connections;
    }

    /*
     * @param mixed The address to listen for incoming connections on.  "0.0.0.0" to listen from anywhere
     * @param int The port to listen to connections on
     * @throws Exception
     * @todo Validate address.  Use socket_get_option, if AF_INET must be IP, if AF_UNIX must be path
     * @todo Should I make handling open/close/msg an application?
     */
    public function run($address = '127.0.0.1', $port = 1025) {
        /* Put this back if I change the server back to Chain of Responsibility
        if (count($this->_receivers) == 0) {
            throw new \RuntimeException("No receiver has been attached to the server");
        }
        */

        set_time_limit(0);
        ob_implicit_flush();

        if (false === ($this->_master->bind($address, (int)$port))) {
            throw new Exception;
        }

        if (false === ($this->_master->listen())) {
            throw new Exception;
        }

        $this->_master->set_nonblock();

        do {
            try {
                $changed     = $this->_resources;
                $num_changed = $this->_master->select($changed, $write = null, $except = null, null);

    			foreach($changed as $resource) {
                    if ($this->_master->getResource() === $resource) {
                        $this->onOpen($this->_master);
                    } else {
                        $conn  = $this->_connections[$resource];
                        $data  = null;
                        $bytes = $conn->recv($data, 4096, 0);

                        if ($bytes == 0) {
                            $this->onClose($conn);
                        } else {
                            $this->onRecv($conn, $data);
                        }
                    }
                }
            } catch (Exception $e) {
                $this->_log->error($e->getMessage());
            } catch (\Exception $fuck) {
                $this->_log->error('Big uh oh: ' . $fuck->getMessage());
            }
        } while (true);

//        $this->_master->set_nonblock();
//        declare(ticks = 1); 
    }

    public function onOpen(SocketInterface $conn) {
        $new_connection     = clone $conn;
        $this->_resources[] = $new_connection->getResource();
        $this->_connections[$new_connection->getResource()] = $new_connection;

        $this->_log->note('New connection, ' . count($this->_connections) . ' total');

        $this->_app->onOpen($new_connection)->execute();
    }

    public function onRecv(SocketInterface $from, $msg) {
        $this->_log->note('New message "' . trim($msg) . '"');

        $this->_app->onRecv($from, $msg)->execute();
    }

    public function onClose(SocketInterface $conn) {
        $resource = $conn->getResource();

        $this->_app->onClose($conn)->execute();

        unset($this->_connections[$resource]);
        unset($this->_resources[array_search($resource, $this->_resources)]);

        $this->_log->note('Connection closed, ' . count($this->_connections) . ' connections remain (' . count($this->_resources) . ')');
    }
}