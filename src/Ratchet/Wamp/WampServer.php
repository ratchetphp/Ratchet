<?php
namespace Ratchet\Wamp;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;
use Ratchet\ConnectionInterface;

class WampServer implements MessageComponentInterface, WsServerInterface {
    /**
     * @var ServerProtocol
     */
    private $wampProtocol;

    /**
     * {@inheritdoc}
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
        $this->wampProtocol->onMessage($conn, $msg);
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