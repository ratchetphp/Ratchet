<?php

namespace Ratchet\Wamp;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;

/**
 * Enable support for the official WAMP sub-protocol in your application
 * WAMP allows for Pub/Sub and RPC
 *
 * @link http://wamp.ws The WAMP specification
 * @link https://github.com/oberstet/autobahn-js Souce for client side library
 * @link http://autobahn.s3.amazonaws.com/js/autobahn.min.js Minified client side library
 */
class WampServer implements MessageComponentInterface, WsServerInterface
{
    protected ServerProtocol $wampProtocol;

    /**
     * This class just makes it 1 step easier to use Topic objects in WAMP
     * If you're looking at the source code, look in the __construct of this
     *  class and use that to make your application instead of using this
     */
    public function __construct(WampServerInterface $app)
    {
        $this->wampProtocol = new ServerProtocol(new TopicManager($app));
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection): void
    {
        $this->wampProtocol->onOpen($connection);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, string $message): void
    {
        try {
            $this->wampProtocol->onMessage($connection, $message);
        } catch (Exception $we) {
            $connection->close(1007);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection): void
    {
        $this->wampProtocol->onClose($connection);
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $exception): void
    {
        $this->wampProtocol->onError($connection, $exception);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubProtocols(): array
    {
        return $this->wampProtocol->getSubProtocols();
    }
}
