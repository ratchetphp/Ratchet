<?php
namespace Ratchet\Server;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\ReactConnection;
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
	 * @throws \Exception
	 */
    public function handleConnect($conn) {
	    $reactConn = new ReactConnection($conn->stream, $this->loop);
	    $reactConn->decor = new IoConnection($reactConn);
	    $reactConn->decor->resourceId = (int)$reactConn->stream;

        $uri = $reactConn->getRemoteAddress();
	    $reactConn->decor->remoteAddress = trim(
            parse_url((strpos($uri, '://') === false ? 'tcp://' : '') . $uri, PHP_URL_HOST),
            '[]'
        );

        $this->app->onOpen($reactConn->decor);

	    $conn->on('data', function ($data) use ($reactConn) {
            $this->handleData($data, $reactConn);
        });
	    $conn->on('close', function () use ($reactConn) {
            $this->handleEnd($reactConn);
        });
	    $conn->on('error', function (\Exception $e) use ($reactConn) {
            $this->handleError($e, $reactConn);
        });
    }

	/**
	 * Data has been received from React
	 * @param string $data
	 * @param ReactConnection $reactConn
	 * @throws \Exception
	 */
    public function handleData($data, $reactConn) {
        try {
            $this->app->onMessage($reactConn->decor, $data);
        } catch (\Exception $e) {
            $this->handleError($e, $reactConn);
        }
    }

	/**
	 * A connection has been closed by React
	 * @param $reactConn
	 * @throws \Exception
	 */
    public function handleEnd($reactConn) {
        try {
            $this->app->onClose($reactConn->decor);
        } catch (\Exception $e) {
            $this->handleError($e, $reactConn);
        }

        unset($reactConn->decor);
    }

	/**
	 * An error has occurred, let the listening application know
	 * @param \Exception $e
	 * @param $reactConn
	 * @throws \Exception
	 */
    public function handleError(\Exception $e, $reactConn) {
        $this->app->onError($reactConn->decor, $e);
    }
}
