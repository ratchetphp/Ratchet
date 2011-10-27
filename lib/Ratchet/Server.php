<?php
namespace Ratchet;
use Ratchet\Server\Aggregator;
use Ratchet\Protocol\ProtocolInterface;
use Ratchet\Logging\LoggerInterface;
use Ratchet\Logging\NullLogger;

/**
 * @todo Consider using _connections as master reference and passing iterator_to_array(_connections) to socket_select
 * @todo Move SocketObserver methods to separate class, create, wrap class in __construct
 */
class Server implements SocketObserver {
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

    protected $_app = null;

    /**
     * @param Ratchet\Socket
     * @param boolean True, enables debug mode and the server doesn't infiniate loop
     * @param Logging\LoggerInterface
     */
    public function __construct(SocketInterface $host, ReceiverInterface $application, LoggerInterface $logger = null) {
        $this->_master = $host;
        $socket = $host->getResource();
        $this->_resources[] = $socket;

        if (null === $logger) {
            $logger = new NullLogger;
        }
        $this->_log = $logger;

        $this->_connections = new \ArrayIterator(array());

        $this->_app = $application;
        $this->_app->setUp($this);
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
     * @deprecated
     * @todo Consider making server chain of responsibility, currently 1-1 relation w/ receivers
     */
    public function attatchReceiver(ReceiverInterface $receiver) {
        $receiver->setUp($this);
        $this->_receivers[spl_object_hash($receiver)] = $receiver;

        return $this;
    }

    /**
     * @return SocketInterface
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

    public function log($msg, $type = 'note') {
        call_user_func(array($this->_log, $type), $msg);
    }

    /*
     * @param mixed
     * @param int
     * @throws Exception
     * @todo Validate address.  Use socket_get_option, if AF_INET must be IP, if AF_UNIX must be path
     * @todo Should I make handling open/close/msg an application?
     */
    public function run($address = '127.0.0.1', $port = 1025) {
/*
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

                        // new Message
                        // $this->_receivers->handleMessage($msg, $conn);
                        }
                    }
                }
            } catch (Exception $e) {
                $this->_log->error($e->getMessage());
            } catch (\Exception $fuck) {
                $this->_log->error('Big uh oh: ' . $e->getMessage());
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
        // /here $this->_receivers->handleConnection($new_connection);
//        $this->tmpRIterator('handleConnect', $new_connection);
    }

    public function onRecv(SocketInterface $from, $msg) {
        $this->_log->note('New message "' . $msg . '"');

        $this->_app->onRecv($from, $msg)->execute();
//        $this->tmpRIterator('handleMessage', $msg, $from);
    }

    public function onClose(SocketInterface $conn) {
        $resource = $conn->getResource();
//        $this->tmpRIterator('handleClose', $conn);
        // $this->_receivers->handleDisconnect($conn);

        $this->_app->onClose($conn)->execute();

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