<?php
namespace Ratchet\Component\Server;
use Ratchet\Component\MessageComponentInterface;
use Ratchet\Resource\Socket\SocketInterface;
use Ratchet\Resource\ConnectionInterface;
use Ratchet\Resource\Connection;
use Ratchet\Resource\Command\CommandInterface;

/**
 * Creates an open-ended socket to listen on a port for incomming connections.  Events are delegated through this to attached applications
 */
class IOServerComponent implements MessageComponentInterface {
    /**
     * @var array of Socket Resources
     */
    protected $_resources = array();

    /**
     * @var array of resources/Connections
     */
    protected $_connections = array();

    /**
     * The decorated application to send events to
     * @var Ratchet\Component\ComponentInterface
     */
    protected $_decorating;

    /**
     * Number of bytes to read in the TCP buffer at a time
     * Default is (currently) 4kb
     * @var int
     */
    protected $_buffer_size = 4096;

    /**
     * After run() is called, the server will loop as long as this is true
     * This is here for unit testing purposes
     * @var bool
     * @internal
     */
    protected $_run = true;

    public function __construct(MessageComponentInterface $component) {
        $this->_decorating = $component;
    }

    /**
     * Set the incoming buffer size in bytes
     * @param int
     * @return App
     * @throws InvalidArgumentException If the parameter is less than 1
     */
    public function setBufferSize($recv_bytes) {
        if ((int)$recv_bytes < 1) {
            throw new \InvalidArgumentException('Invalid number of bytes set, must be more than 0');
        }

        $this->_buffer_size = (int)$recv_bytes;

        return $this;
    }

    /*
     * Run the server infinitely
     * @param Ratchet\Resource\Socket\SocketInterface
     * @param mixed The address to listen for incoming connections on.  "0.0.0.0" to listen from anywhere
     * @param int The port to listen to connections on (make sure to run as root if < 1000)
     * @throws Ratchet\Exception
     */
    public function run(SocketInterface $host, $address = '127.0.0.1', $port = 1025) {
        $this->_connections[$host->getResource()] = new Connection($host);
        $this->_resources[] = $host->getResource();

        gc_enable();
        set_time_limit(0);
        ob_implicit_flush();

        declare(ticks = 1);

        $host->set_option(SOL_SOCKET, SO_SNDBUF, $this->_buffer_size);
        $host->set_nonblock()->bind($address, (int)$port)->listen();

        do {
            $this->loop($host);
        } while ($this->_run);
    }

    protected function loop(SocketInterface $host) {
        $changed = $this->_resources;

        try {
            $write = $except = null;

            $num_changed = $host->select($changed, $write, $except, null);
        } catch (Exception $e) {
            // master had a problem?...what to do?
            return;
        }

        foreach($changed as $resource) {
            try {
                $conn = $this->_connections[$resource];

                if ($host->getResource() === $resource) {
                    $res = $this->onOpen($conn);
                } else {
                    $data  = $buf = '';
                    $bytes = $conn->getSocket()->recv($buf, $this->_buffer_size, MSG_DONTWAIT);
                    if ($bytes > 0) {
                        $data = $buf;

                        // This idea works* but...
                        // 1) A single DDOS attack will block the entire application (I think)
                        // 2) What if the last message in the frame is equal to $recv_bytes?  Would loop until another msg is sent
                        // 3) This failed...an intermediary can set their buffer lower and this still propagates a fragment
                        // Need to 1) proc_open the recv() calls.  2) ???

                        /*
                        while ($bytes === $recv_bytes) {
                            $bytes = $conn->recv($buf, $recv_bytes, 0);
                            $data .= $buf;
                        }
                        */

                        $res = $this->onMessage($conn, $data);
                    } else {
                        $res = $this->onClose($conn);
                    }
                }
            } catch (\Exception $e) {
                $res = $this->onError($conn, $e);
            }

            while ($res instanceof CommandInterface) {
                try {
                    $new_res = $res->execute($this);
                } catch (\Exception $e) {
                    break;
                    // trigger new error
                    // $new_res = $this->onError($e->getSocket()); ???
                    // this is dangerous territory...could get in an infinte loop...Exception might not be Ratchet\Exception...$new_res could be ActionInterface|Composite|NULL...
                }

                $res = $new_res;
            }
        }
    }

    /**
     * @{inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
        $new_socket     = clone $conn->getSocket();
        $new_socket->set_nonblock();
        $new_connection = new Connection($new_socket);

        $this->_resources[] = $new_connection->getSocket()->getResource();
        $this->_connections[$new_connection->getSocket()->getResource()] = $new_connection;

        return $this->_decorating->onOpen($new_connection);
    }

    /**
     * @{inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        return $this->_decorating->onMessage($from, $msg);
    }

    /**
     * @{inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        $resource = $conn->getSocket()->getResource();

        $cmd = $this->_decorating->onClose($conn);

        unset($this->_connections[$resource], $this->_resources[array_search($resource, $this->_resources)]);

        return $cmd;
    }

    /**
     * @{inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        return $this->_decorating->onError($conn, $e);
    }
}