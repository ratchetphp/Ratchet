<?php
namespace Ratchet\Server;
use Ratchet\MessageComponentInterface;
use Ratchet\Traits\DynamicPropertiesTrait;
use React\EventLoop\LoopInterface;
use React\Socket\ServerInterface;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as Reactor;
use React\Socket\SecureServer as SecureReactor;

/**
 * Creates an open-ended socket to listen on a port for incoming connections.
 * Events are delegated through this to attached applications
 */
class IoServer {
    use DynamicPropertiesTrait;

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
     * @param \Ratchet\MessageComponentInterface  $app      The Ratchet application stack to host
     * @param \React\Socket\ServerInterface       $socket   The React socket server to run the Ratchet application off of
     * @param \React\EventLoop\LoopInterface|null $loop     The React looper to run the Ratchet application off of
     */
    public function __construct(MessageComponentInterface $app, ServerInterface $socket, LoopInterface $loop = null) {
        if (false === strpos(PHP_VERSION, "hiphop")) {
            gc_enable();
        }

        set_time_limit(0);
        ob_implicit_flush();

        $this->loop = $loop;
        $this->app  = $app;
        $this->socket = $socket;

        $socket->on('connection', array($this, 'handleConnect'));
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
        $io_conn = new IoConnection($conn);
        $io_conn->resourceId = (int)$conn->stream;

        $uri = $conn->getRemoteAddress();
        $io_conn->remoteAddress = trim(
            parse_url((strpos($uri, '://') === false ? 'tcp://' : '') . $uri, PHP_URL_HOST),
            '[]'
        );

        $this->app->onOpen($io_conn);

        $conn->on('data', function ($data) use ($io_conn) {
            $this->handleData($data, $io_conn);
        });
        $conn->on('close', function () use ($io_conn) {
            $this->handleEnd($io_conn);
        });
        $conn->on('error', function (\Exception $e) use ($io_conn) {
            $this->handleError($e, $io_conn);
        });
    }

    /**
     * Data has been received from React
     * @param string                            $data
     * @param \Ratchet\ConnectionInterface $io_conn
     */
    public function handleData($data, $io_conn) {
        try {
            $this->app->onMessage($io_conn, $data);
        } catch (\Exception $e) {
            $this->handleError($e, $io_conn);
        }
    }

    /**
     * A connection has been closed by React
     * @param \Ratchet\ConnectionInterface $io_conn
     */
    public function handleEnd($io_conn) {
        try {
            $this->app->onClose($io_conn);
        } catch (\Exception $e) {
            $this->handleError($e, $io_conn);
        }

        unset($io_conn);
    }

    /**
     * An error has occurred, let the listening application know
     * @param \Exception                        $e
     * @param \Ratchet\ConnectionInterface $io_conn
     */
    public function handleError(\Exception $e, $io_conn) {
        $this->app->onError($io_conn, $e);
    }
}
