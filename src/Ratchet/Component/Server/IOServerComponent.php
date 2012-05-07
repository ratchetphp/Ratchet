<?php
namespace Ratchet\Component\Server;
use Ratchet\Component\MessageComponentInterface;
use Ratchet\Resource\ConnectionInterface;
use React\EventLoop\LoopInterface;
use React\Socket\ServerInterface;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as Reactor;

/**
 * Creates an open-ended socket to listen on a port for incomming connections.  Events are delegated through this to attached applications
 */
class IOServerComponent {
    /**
     * @var React\EventLoop\LoopInterface
     */
    public $loop;

    public function __construct(MessageComponentInterface $app, ServerInterface $socket, LoopInterface $loop) {
        $this->loop = $loop;

        $that = $this;

        $socket->on('connect', function($conn) use ($app, $that) {
            $decor = new IoConnection($conn, $that);

            $decor->resourceId    = (int)$conn->socket;
            $decor->remoteAddress = '127.0.0.1'; // todo

            $app->onOpen($decor);

            $conn->on('data', function($data) use ($decor, $app) {
                $app->onMessage($decor, $data);
            });

            $conn->on('error', function($e) use ($decor, $app) {
                $app->onError($decor, $e);
            });

            $conn->on('end', function() use ($decor, $app) {
                $app->onClose($decor);
            });
        });
    }

    public static function factory(MessageComponentInterface $component, $port = 80, $address = '0.0.0.0') {
        $loop   = LoopFactory::create();
        $socket = new Reactor($loop);
        $socket->listen($port, $address);
        $server = new self($component, $socket, $loop);

        return $server;
    }

    public function run() {
        $this->loop->run();
    }
}