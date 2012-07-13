<?php
namespace Ratchet\Server;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;
use React\Socket\ServerInterface;
use React\EventLoop\StreamSelectLoop;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as Reactor;

/**
 * Creates an open-ended socket to listen on a port for incomming connections.  Events are delegated through this to attached applications
 */
class IoServer {
    /**
     * @var React\EventLoop\LoopInterface
     */
    public $loop;

    /**
     * @var Ratchet\MessageComponentInterface
     */
    public $app;

    /**
     * Array of React event handlers
     * @var array
     */
    protected $handlers = array();

    /**
     * @param Ratchet\MessageComponentInterface The Ratchet application stack to host
     * @param React\Socket\ServerInterface The React socket server to run the Ratchet application off of
     * @param React\EventLoop\LoopInterface The React looper to run the Ratchet application off of
     */
    public function __construct(MessageComponentInterface $app, ServerInterface $socket, LoopInterface $loop) {
        gc_enable();
        set_time_limit(0);
        ob_implicit_flush();

        $this->loop = $loop;
        $this->app  = $app;

        $socket->on('connection', array($this, 'handleConnect'));

        $this->handlers['data']  = array($this, 'handleData');
        $this->handlers['end']   = array($this, 'handleEnd');
        $this->handlers['error'] = array($this, 'handleError');
    }

    public static function factory(MessageComponentInterface $component, $port = 80, $address = '0.0.0.0') {
        $loop   = new StreamSelectLoop;
        $socket = new Reactor($loop);
        $socket->listen($port, $address);

        return new static($component, $socket, $loop);
    }

    public function run() {
        $this->loop->run();
    }

    public function handleConnect($conn) {
        $conn->decor = new IoConnection($conn, $this);

        $conn->decor->resourceId    = (int)$conn->stream;
        $conn->decor->remoteAddress = $conn->getRemoteAddress();

        $this->app->onOpen($conn->decor);

        $conn->on('data', $this->handlers['data']);
        $conn->on('end', $this->handlers['end']);
        $conn->on('error', $this->handlers['error']);
    }

    public function handleData($data, $conn) {
        try {
            $this->app->onMessage($conn->decor, $data);
        } catch (\Exception $e) {
            $this->handleError($e, $conn);
        }
    }

    public function handleEnd($conn) {
        try {
            $this->app->onClose($conn->decor);
        } catch (\Exception $e) {
            $this->handleError($e, $conn);
        }

        unset($conn->decor);
    }

    public function handleError(\Exception $e, $conn) {
        $this->app->onError($conn->decor, $e);
    }
}