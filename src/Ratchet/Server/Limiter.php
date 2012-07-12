<?php
namespace Ratchet\Server;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServerInterface;

class Limiter implements MessageComponentInterface, WsServerInterface {
    /**
     * @var Ratchet\MessageComponentInterface
     */
    protected $app;

    protected $settings = array(
        'maxConnections'           => 10000
      , 'maxConnectionsPerAddress' => 20
      , 'maxDataPerInterval'       => array(1048576, 60)
      , 'maxDuration'              => 28800
    );

    protected $connections = 0;

    /**
     * @param Ratchet\MessageComponentInterface
     */
    public function __construct(MessageComponentInterface $app) {
        $this->app = $app;
    }

    /**
     * @param int
     */
    public function maxConnections($num) {
        $this->settings['maxConnections'] = (int)$num;
    }

/*
    public function maxConnectionsPerAddress($num) {
    }

    public function maxDataPerInterval($size, $seconds) {
    }

    public function maxDuration($seconds) {
    }
*/

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
        $this->connections++;

        if ($this->connections > $this->settings['maxConnections']) {
            return $conn->close();
        }

        $this->app->onOpen($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        $this->app->onMessage($from, $msg);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        $this->connections--;

        $this->app->onClose($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->app->onError($conn, $e);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubProtocols() {
        return ($this->app instanceof WsServerInterface ? $this->app->getSubProtocols() : array());
    }
}