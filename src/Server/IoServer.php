<?php

namespace Ratchet\Server;

use Ratchet\MessageComponentInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ServerInterface;
use React\Socket\SocketServer;

/**
 * Creates an open-ended socket to listen on a port for incoming connections.
 * Events are delegated through this to attached applications
 */
class IoServer
{
    /**
     * @param  \Ratchet\MessageComponentInterface  $app The Ratchet application stack to host
     * @param  \React\Socket\ServerInterface  $socket The React socket server to run the Ratchet application off of
     * @param  \React\EventLoop\LoopInterface|null  $loop The React looper to run the Ratchet application off of
     */
    public function __construct(
        public MessageComponentInterface $app,
        public ServerInterface $socket,
        public ?LoopInterface $loop = null,
    ) {
        if (strpos(PHP_VERSION, 'hiphop') === false) {
            gc_enable();
        }

        set_time_limit(0);
        ob_implicit_flush();

        $socket->on('connection', [$this, 'handleConnect']);
    }

    /**
     * @param  \Ratchet\MessageComponentInterface  $component  The application that I/O will call when events are received
     * @param  int  $port       The port to server sockets on
     * @param  string  $address    The address to receive sockets on (0.0.0.0 means receive connections from any)
     */
    public static function factory(
        MessageComponentInterface $component,
        int $port = 80,
        string $address = '0.0.0.0',
    ): IoServer {
        $loop = Loop::get();
        $socket = new SocketServer($address.':'.$port, [], $loop);

        return new static($component, $socket, $loop);
    }

    /**
     * Run the application by entering the event loop
     *
     * @throws \RuntimeException If a loop was not previously specified
     */
    public function run()
    {
        if ($this->loop === null) {
            throw new \RuntimeException('A React Loop was not provided during instantiation');
        }

        // @codeCoverageIgnoreStart
        $this->loop->run();
        // @codeCoverageIgnoreEnd
    }

    /**
     * Triggered when a new connection is received from React
     */
    public function handleConnect(ConnectionInterface $connection)
    {
        $connection->decor = new IoConnection($connection);
        $connection->decor->resourceId = (int) $connection->stream;

        $uri = $connection->getRemoteAddress();
        $connection->decor->remoteAddress = trim(
            parse_url((strpos($uri, '://') === false ? 'tcp://' : '').$uri, PHP_URL_HOST),
            '[]'
        );

        $this->app->onOpen($connection->decor);

        $connection->on('data', function ($data) use ($connection) {
            $this->handleData($data, $connection);
        });
        $connection->on('close', function () use ($connection) {
            $this->handleEnd($connection);
        });
        $connection->on('error', function (\Exception $exception) use ($connection) {
            $this->handleError($exception, $connection);
        });
    }

    /**
     * Data has been received from React
     */
    public function handleData(string $data, ConnectionInterface $connection)
    {
        try {
            $this->app->onMessage($connection->decor, $data);
        } catch (\Exception $exception) {
            $this->handleError($exception, $connection);
        }
    }

    /**
     * A connection has been closed by React
     */
    public function handleEnd(ConnectionInterface $connection): void
    {
        try {
            $this->app->onClose($connection->decor);
        } catch (\Exception $exception) {
            $this->handleError($exception, $connection);
        }

        unset($connection->decor);
    }

    /**
     * An error has occurred, let the listening application know
     */
    public function handleError(\Exception $exception, ConnectionInterface $connection)
    {
        $this->app->onError($connection->decor, $exception);
    }
}
