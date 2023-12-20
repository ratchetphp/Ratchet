<?php

namespace Ratchet\WebSocket;

use GuzzleHttp\Psr7\Message;
use Psr\Http\Message\RequestInterface;
use Ratchet\ComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\CloseResponseTrait;
use Ratchet\Http\HttpServerInterface;
use Ratchet\MessageComponentInterface as DataComponentInterface;
use Ratchet\RFC6455\Handshake\RequestVerifier;
use Ratchet\RFC6455\Handshake\ServerNegotiator;
use Ratchet\RFC6455\Messaging\CloseFrameChecker;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\RFC6455\Messaging\FrameInterface;
use Ratchet\RFC6455\Messaging\MessageBuffer;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\LoopInterface;

/**
 * The adapter to handle WebSocket requests/responses
 * This is a mediator between the Server and your application to handle real-time messaging through a web browser
 *
 * @link http://ca.php.net/manual/en/ref.http.php
 * @link http://dev.w3.org/html5/websockets/
 */
class WsServer implements HttpServerInterface
{
    use CloseResponseTrait;

    private ComponentInterface $delegate;

    protected \SplObjectStorage $connections;

    private \Ratchet\RFC6455\Messaging\CloseFrameChecker $closeFrameChecker;

    private \Ratchet\RFC6455\Handshake\ServerNegotiator $handshakeNegotiator;

    private \Closure $ueFlowFactory;

    private \Closure $pongReceiver;

    private \Closure $messageComponent;

    /**
     * @param  \Ratchet\WebSocket\MessageComponentInterface|\Ratchet\MessageComponentInterface  $component Your application to run with WebSockets
     *
     * @note If you want to enable sub-protocols have your component implement WsServerInterface as well
     */
    public function __construct(MessageComponentInterface|DataComponentInterface $component)
    {
        if ($component instanceof MessageComponentInterface) {
            $this->messageComponent = function (ConnectionInterface $connection, MessageInterface $message) {
                $this->delegate->onMessage($connection, $message);
            };
        } elseif ($component instanceof DataComponentInterface) {
            $this->messageComponent = function (ConnectionInterface $connection, MessageInterface $message) {
                $this->delegate->onMessage($connection, $message->getPayload());
            };
        } else {
            throw new \UnexpectedValueException('Expected instance of \Ratchet\WebSocket\MessageComponentInterface or \Ratchet\MessageComponentInterface');
        }

        if (bin2hex('✓') !== 'e29c93') {
            throw new \DomainException('Bad encoding, unicode character ✓ did not match expected value. Ensure charset UTF-8 and check ini val mbstring.func_autoload');
        }

        $this->delegate = $component;
        $this->connections = new \SplObjectStorage;

        $this->closeFrameChecker = new CloseFrameChecker;
        $this->handshakeNegotiator = new ServerNegotiator(new RequestVerifier);
        $this->handshakeNegotiator->setStrictSubProtocolCheck(true);

        if ($component instanceof WsServerInterface) {
            $this->handshakeNegotiator->setSupportedSubProtocols($component->getSubProtocols());
        }

        $this->pongReceiver = function () {
        };

        $reusableUnderflowException = new \UnderflowException;
        $this->ueFlowFactory = function () use ($reusableUnderflowException) {
            return $reusableUnderflowException;
        };
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection, ?RequestInterface $request = null)
    {
        if ($request === null) {
            throw new \UnexpectedValueException('$request can not be null');
        }

        $connection->httpRequest = $request;

        $connection->WebSocket = new \StdClass;
        $connection->WebSocket->closing = false;

        $response = $this->handshakeNegotiator->handshake($request)->withHeader('X-Powered-By', 'Ratchet/0.4.4');

        $connection->send(Message::toString($response));

        if ($response->getStatusCode() !== 101) {
            return $connection->close();
        }

        $wsConn = new WsConnection($connection);

        $streamer = new MessageBuffer(
            $this->closeFrameChecker,
            function (MessageInterface $message) use ($wsConn) {
                $cb = $this->messageComponent;
                $cb($wsConn, $message);
            },
            function (FrameInterface $frame) use ($wsConn) {
                $this->onControlFrame($frame, $wsConn);
            },
            true,
            $this->ueFlowFactory
        );

        $this->connections->attach($connection, new ConnectionContext($wsConn, $streamer));

        return $this->delegate->onOpen($wsConn);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, string $message)
    {
        if ($connection->WebSocket->closing) {
            return;
        }

        $this->connections[$connection]->buffer->onData($message);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        if ($this->connections->contains($connection)) {
            $context = $this->connections[$connection];
            $this->connections->detach($connection);

            $this->delegate->onClose($context->connection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $exception)
    {
        if ($this->connections->contains($connection)) {
            $this->delegate->onError($this->connections[$connection]->connection, $exception);
        } else {
            $connection->close();
        }
    }

    public function onControlFrame(FrameInterface $frame, WsConnection $connection)
    {
        switch ($frame->getOpCode()) {
            case Frame::OP_CLOSE:
                $connection->close($frame);
                break;
            case Frame::OP_PING:
                $connection->send(new Frame($frame->getPayload(), true, Frame::OP_PONG));
                break;
            case Frame::OP_PONG:
                $pongReceiver = $this->pongReceiver;
                $pongReceiver($frame, $connection);
                break;
        }
    }

    public function setStrictSubProtocolCheck($enable)
    {
        $this->handshakeNegotiator->setStrictSubProtocolCheck($enable);
    }

    public function enableKeepAlive(LoopInterface $loop, $interval = 30)
    {
        $lastPing = new Frame(uniqid(), true, Frame::OP_PING);
        $pingedConnections = new \SplObjectStorage;
        $splClearer = new \SplObjectStorage;

        $this->pongReceiver = function (FrameInterface $frame, $wsConn) use ($pingedConnections, &$lastPing) {
            if ($frame->getPayload() === $lastPing->getPayload()) {
                $pingedConnections->detach($wsConn);
            }
        };

        $loop->addPeriodicTimer((int) $interval, function () use ($pingedConnections, &$lastPing, $splClearer) {
            foreach ($pingedConnections as $wsConn) {
                $wsConn->close();
            }
            $pingedConnections->removeAllExcept($splClearer);

            $lastPing = new Frame(uniqid(), true, Frame::OP_PING);

            foreach ($this->connections as $key => $connection) {
                $wsConn = $this->connections[$connection]->connection;

                $wsConn->send($lastPing);
                $pingedConnections->attach($wsConn);
            }
        });
    }
}
