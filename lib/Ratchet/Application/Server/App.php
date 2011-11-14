<?php
namespace Ratchet\Application\Server;
use Ratchet\Application\ApplicationInterface;
use Ratchet\SocketInterface;
use Ratchet\Resource\Connection;
use Ratchet\Resource\Command\CommandInterface;

/**
 * Creates an open-ended socket to listen on a port for incomming connections.  Events are delegated through this to attached applications
 * @todo Consider using _connections as master reference and passing iterator_to_array(_connections) to socket_select
 * @todo Currently passing Socket object down the decorated chain - should be sending reference to it instead; Receivers do not interact with the Socket directly, they do so through the Command pattern
 * @todo With all these options for the server I should probably use a DIC
 */
class App implements ApplicationInterface {
    /**
     * @var array of Socket Resources
     */
    protected $_resources = array();

    /**
     * @var array of resources/Connections
     */
    protected $_connections = array();

    /**
     * @var Ratchet\Application\ApplicationInterface
     * Maybe temporary?
     */
    protected $_app;

    /**
     * @param Ratchet\ObserverInterface
     */
    public function __construct(ApplicationInterface $application = null) {
        if (null === $application) {
            throw new \UnexpectedValueException("Server requires an application to run off of");
        }

        $this->_app = $application;
    }

    /*
     * @param Ratchet\SocketInterface
     * @param mixed The address to listen for incoming connections on.  "0.0.0.0" to listen from anywhere
     * @param int The port to listen to connections on
     * @throws Ratchet\Exception
     * @todo Validate address.  Use socket_get_option, if AF_INET must be IP, if AF_UNIX must be path
     * @todo Consider making the 4kb listener changable
     */
    public function run(SocketInterface $host, $address = '127.0.0.1', $port = 1025) {
        $this->_connections[$host->getResource()] = new Connection($host);
        $this->_resources[] = $host->getResource();

        $recv_bytes = 1024;

        set_time_limit(0);
        ob_implicit_flush();

        $host->set_nonblock();
        declare(ticks = 1);

        if (false === ($host->bind($address, (int)$port))) {
            throw new Exception($host);
        }

        if (false === ($host->listen())) {
            throw new Exception($host);
        }

        do {
            $changed = $this->_resources;

            try {
                $num_changed = $host->select($changed, $write = null, $except = null, null);
            } catch (Exception $e) {
                // master had a problem?...what to do?
                continue;
            }

			foreach($changed as $resource) {
                try {
                    $conn = $this->_connections[$resource];

                    if ($host->getResource() === $resource) {
                        $res = $this->onOpen($conn);
                    } else {
                        $data  = $buf = '';
                        $bytes = $conn->getSocket()->recv($buf, $recv_bytes, 0);
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

                            $res = $this->onRecv($conn, $data);
                        } else {
                            $res = $this->onClose($conn);
                        }
                    }
                } catch (Exception $se) {
                    $res = $this->onError($se->getSocket(), $se); // Just in case...but I don't think I need to do this
                } catch (\Exception $e) {
                    $res = $this->onError($conn, $e);
                }

                while ($res instanceof CommandInterface) {
                    try {
                        $new_res = $res->execute($this);
                    } catch (\Exception $e) {
                        // trigger new error
                        // $new_res = $this->onError($e->getSocket()); ???
                        // this is dangerous territory...could get in an infinte loop...Exception might not be Ratchet\Exception...$new_res could be ActionInterface|Composite|NULL...
                    }

                    $res = $new_res;
                }
            }
        } while (true);
    }

    public function onOpen(Connection $conn) {
        $new_socket     = clone $conn->getSocket();
        $new_connection = new Connection($new_socket);

        $this->_resources[] = $new_connection->getSocket()->getResource();
        $this->_connections[$new_connection->getSocket()->getResource()] = $new_connection;

        return $this->_app->onOpen($new_connection);
    }

    public function onRecv(Connection $from, $msg) {
        return $this->_app->onRecv($from, $msg);
    }

    public function onClose(Connection $conn) {
        $resource = $conn->getSocket()->getResource();

        $cmd = $this->_app->onClose($conn);

        unset($this->_connections[$resource], $this->_resources[array_search($resource, $this->_resources)]);

        return $cmd;
    }

    public function onError(Connection $conn, \Exception $e) {
        return $this->_app->onError($conn, $e);
    }
}