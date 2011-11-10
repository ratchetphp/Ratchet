<?php
namespace Ratchet;
use Ratchet\Server\Aggregator;
use Ratchet\Protocol\ProtocolInterface;
use Ratchet\Command\CommandInterface;

/**
 * Creates an open-ended socket to listen on a port for incomming connections.  Events are delegated through this to attached applications
 * @todo Consider using _connections as master reference and passing iterator_to_array(_connections) to socket_select
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
    protected $_resources = array();

    /**
     * @var ArrayIterator of Resouces (Socket type) as keys, Ratchet\Socket as values
     */
    protected $_connections;

    /**
     * @var SocketObserver
     * Maybe temporary?
     */
    protected $_app;

    /**
     * @param Ratchet\Socket
     * @param SocketObserver
     */
    public function __construct(SocketInterface $host, SocketObserver $application) {
        $this->_master = $host;
        $socket = $host->getResource();
        $this->_resources[] = $socket;

        $this->_connections = new \ArrayIterator(array());

        $this->_app = $application;
    }

    /**
     * @return ArrayIterator of SocketInterfaces
     * @todo This interface was originally in place as Server was passed up/down chain, but isn't anymore, consider removing
     */
    public function getIterator() {
        return $this->_connections;
    }

    /*
     * @param mixed The address to listen for incoming connections on.  "0.0.0.0" to listen from anywhere
     * @param int The port to listen to connections on
     * @throws Exception
     * @todo Validate address.  Use socket_get_option, if AF_INET must be IP, if AF_UNIX must be path
     * @todo Consider making the 4kb listener changable
     */
    public function run($address = '127.0.0.1', $port = 1025) {
        set_time_limit(0);
        ob_implicit_flush();

        if (false === ($this->_master->bind($address, (int)$port))) {
            throw new Exception($this->_master);
        }

        if (false === ($this->_master->listen())) {
            throw new Exception($this->_master);
        }

        $this->_master->set_nonblock();
        declare(ticks = 1); 

        do {
            try {
                $changed     = $this->_resources;
                $num_changed = $this->_master->select($changed, $write = null, $except = null, null);

    			foreach($changed as $resource) {
                    if ($this->_master->getResource() === $resource) {
                        $res = $this->onOpen($this->_master);
                    } else {
                        $conn  = $this->_connections[$resource];
                        $data  = null;
                        $bytes = $conn->recv($data, 4096, 0);

                        if ($bytes == 0) {
                            $res = $this->onClose($conn);
                        } else {
                            $res = $this->onRecv($conn, $data);
                        }
                    }

                    while ($res instanceof CommandInterface) {
                        $res = $res->execute($this);
                    }
                }
            } catch (Exception $se) {
                // Instead of logging error, will probably add/trigger onIOError/onError or something in SocketObserver

                // temporary, move to application
                if ($se->getCode() != 35) {
                    $close = new \Ratchet\Command\Action\CloseConnection($se->getSocket());
                    $close->execute($this);
                }
            } catch (\Exception $e) {
                // onError() - but can I determine which is/was the target Socket that threw the exception...?
                // $conn->close() ???
            }
        } while (true);
    }

    public function onOpen(SocketInterface $conn) {
        $new_connection     = clone $conn;
        $this->_resources[] = $new_connection->getResource();
        $this->_connections[$new_connection->getResource()] = $new_connection;

        return $this->_app->onOpen($new_connection);
    }

    public function onRecv(SocketInterface $from, $msg) {
        return $this->_app->onRecv($from, $msg);
    }

    public function onClose(SocketInterface $conn) {
        $resource = $conn->getResource();

        $cmd = $this->_app->onClose($conn);

        unset($this->_connections[$resource]);
        unset($this->_resources[array_search($resource, $this->_resources)]);

        return $cmd;
    }
}