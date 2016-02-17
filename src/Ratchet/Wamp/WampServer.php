<?php
namespace Ratchet\Wamp;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;
use Ratchet\ConnectionInterface;

/**
 * Enable support for the official WAMP sub-protocol in your application
 * WAMP allows for Pub/Sub and RPC
 * @link http://wamp.ws The WAMP specification
 * @link https://github.com/oberstet/AutobahnJS Souce for client side library
 * @link http://autobahn.s3.amazonaws.com/js/autobahn.min.js Minified client side library
 */
class WampServer implements MessageComponentInterface, WsServerInterface {
    /**
     * @var ServerProtocol
     */
    protected $wampProtocol;

    /**
     * This class just makes it 1 step easier to use Topic objects in WAMP
     * If you're looking at the source code, look in the __construct of this
     *  class and use that to make your application instead of using this
     */
    public function __construct(WampServerInterface $app) {
        $this->wampProtocol = new ServerProtocol(new TopicManager($app));
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
        $this->wampProtocol->onOpen($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $conn, $msg) {
        try {
            $this->wampProtocol->onMessage($conn, $msg);
        } catch (Exception $we) {
            $conn->close(1007);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        $this->wampProtocol->onClose($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->wampProtocol->onError($conn, $e);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubProtocols() {
        return $this->wampProtocol->getSubProtocols();
    }
}
