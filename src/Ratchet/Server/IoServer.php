<?php
namespace Ratchet\Server;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use React\Socket\ServerInterface;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as Reactor;

/**
 * Creates an open-ended socket to listen on a port for incoming connections.
 * Events are delegated through this to attached applications
 */
class IoServer {
    /**
     * @var \React\EventLoop\LoopInterface
     */
    public $loop;

    /**
     * @var \Ratchet\MessageComponentInterface
     */
    public $app;

    /**
     * The socket server the Ratchet Application is run off of
     * @var \React\Socket\ServerInterface
     */
    public $socket;

    /**
     * @var IoConnection[]
     */
    private $connections;

    /**
     * @param \Ratchet\MessageComponentInterface  $app      The Ratchet application stack to host
     * @param \React\Socket\ServerInterface       $socket   The React socket server to run the Ratchet application off of
     * @param \React\EventLoop\LoopInterface|null $loop     The React looper to run the Ratchet application off of
     */
    public function __construct(MessageComponentInterface $app, ServerInterface $socket, LoopInterface $loop = null) {
        if (false === strpos(PHP_VERSION, 'hiphop')) {
            gc_enable();
        }

        set_time_limit(0);
        ob_implicit_flush();

        $this->loop = $loop;
        $this->app  = $app;
        $this->socket = $socket;
        $this->connections = new \SplObjectStorage;

        $socket->on('connection', [$this, 'handleConnect']);
    }

    /**
     * @param  \Ratchet\MessageComponentInterface $component  The application that I/O will call when events are received
     * @param  int                                $port       The port to server sockets on
     * @param  string                             $address    The address to receive sockets on (0.0.0.0 means receive connections from any)
     * @return IoServer
     */
    public static function factory(MessageComponentInterface $component, $port = 80, $address = '0.0.0.0') {
        $loop   = LoopFactory::create();
        $socket = new Reactor($address . ':' . $port, $loop);

        return new static($component, $socket, $loop);
    }

    /**
     * Run the application by entering the event loop
     * @throws \RuntimeException If a loop was not previously specified
     */
    public function run() {
        if (null === $this->loop) {
            throw new \RuntimeException("A React Loop was not provided during instantiation");
        }

        // @codeCoverageIgnoreStart
        $this->loop->run();
        // @codeCoverageIgnoreEnd
    }

    /**
     * Triggered when a new connection is received from React
     * @param \React\Socket\ConnectionInterface $conn
     */
    public function handleConnect($conn) {
        $connContainer = new IoConnection($conn);
        $this->connections->attach($conn, $connContainer);

//        $conn->decor = new IoConnection($conn);

        // TODO: @deprecated
        $connContainer->resourceId = (int)$conn->stream;
        $connContainer->remoteAddress = $connContainer->get('Socket.remoteAddress');

        $this->app->onOpen($connContainer); // TODO: Try/catch ->onError ?

        $conn->on('data', function ($data) use ($conn) {
            $this->handleData($data, $conn);
        });
        $conn->on('close', function () use ($conn) {
            $this->handleEnd($conn);
        });
        $conn->on('error', function (\Exception $e) use ($connContainer) {
            $this->handleError($e, $connContainer);
        });
    }

    /**
     * Data has been received from React
     * @param string                            $data
     * @param \React\Socket\ConnectionInterface $conn
     */
    public function handleData($data, $conn) {
        try {
            $this->app->onMessage($this->connections[$conn], $data);
        } catch (\Exception $e) {
            $this->handleError($e, $this->connections[$conn]);
        }
    }

    /**
     * A connection has been closed by React
     * @param \React\Socket\ConnectionInterface $conn
     */
    public function handleEnd($conn) {
        $connContainer = $this->connections[$conn];

        $this->connections->detach($conn);

        try {
            $this->app->onClose($connContainer);
        } catch (\Exception $e) {
            $this->handleError($e, $connContainer);
        }
    }

    /**
     * An error has occurred, let the listening application know
     * @param \Exception                     $e
     * @param \Ratchet\ConnectionInterface $conn
     */
    public function handleError(\Exception $e, ConnectionInterface $conn) {
        $this->app->onError($conn, $e); // TODO: Try/catch? log? Retry with limit?
    }
}
