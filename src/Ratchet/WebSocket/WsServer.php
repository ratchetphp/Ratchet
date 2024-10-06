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
 * @link http://ca.php.net/manual/en/ref.http.php
 * @link http://dev.w3.org/html5/websockets/
 */
class WsServer implements HttpServerInterface
{
    use CloseResponseTrait;

    /**
     * Decorated component
     */
    private ComponentInterface $delegate;

    protected \SplObjectStorage $connections;

    private CloseFrameChecker $closeFrameChecker;

    private ServerNegotiator $handshakeNegotiator;

    private \Closure $ueFlowFactory;

    private \Closure $pongReceiver;

    /**
     * @var \Closure
     */
    private $msgCb;

    /**
     * @param \Ratchet\WebSocket\MessageComponentInterface|\Ratchet\MessageComponentInterface $component Your application to run with WebSockets
     * @note If you want to enable sub-protocols have your component implement WsServerInterface as well
     */
    public function __construct(ComponentInterface $component) {
        if ($component instanceof MessageComponentInterface) {
            $this->msgCb = function(ConnectionInterface $conn, MessageInterface $msg): void {
                $this->delegate->onMessage($conn, $msg);
            };
        } elseif ($component instanceof DataComponentInterface) {
            $this->msgCb = function(ConnectionInterface $conn, MessageInterface $msg): void {
                $this->delegate->onMessage($conn, $msg->getPayload());
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

        $this->pongReceiver = function(): void {};

        $reusableUnderflowException = new \UnderflowException;
        $this->ueFlowFactory = fn(): \UnderflowException => $reusableUnderflowException;
    }

    #[\Override]
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        if (null === $request) {
            throw new \UnexpectedValueException('$request can not be null');
        }

        $conn->httpRequest = $request;

        $conn->WebSocket = new \StdClass;
        $conn->WebSocket->closing = false;

        $response = $this->handshakeNegotiator->handshake($request)->withHeader('X-Powered-By', \Ratchet\VERSION);

        $conn->send(Message::toString($response));

        if (101 !== $response->getStatusCode()) {
            return $conn->close();
        }

        $wsConn = new WsConnection($conn);

        $streamer = new MessageBuffer(
            $this->closeFrameChecker,
            function(MessageInterface $msg) use ($wsConn): void {
                $cb = $this->msgCb;
                $cb($wsConn, $msg);
            },
            function(FrameInterface $frame) use ($wsConn): void {
                $this->onControlFrame($frame, $wsConn);
            },
            true,
            $this->ueFlowFactory
        );

        $this->connections->attach($conn, new ConnContext($wsConn, $streamer));

        return $this->delegate->onOpen($wsConn);
    }

    #[\Override]
    public function onMessage(ConnectionInterface $from, $msg) {
        if ($from->WebSocket->closing) {
            return;
        }

        $this->connections[$from]->buffer->onData($msg);
    }

    #[\Override]
    public function onClose(ConnectionInterface $conn) {
        if ($this->connections->contains($conn)) {
            $context = $this->connections[$conn];
            $this->connections->detach($conn);

            $this->delegate->onClose($context->connection);
        }
    }

    #[\Override]
    public function onError(ConnectionInterface $conn, \Exception $e) {
        if ($this->connections->contains($conn)) {
            $this->delegate->onError($this->connections[$conn]->connection, $e);
        } else {
            $conn->close();
        }
    }

    public function onControlFrame(FrameInterface $frame, WsConnection $conn): void {
        switch ($frame->getOpCode()) {
            case Frame::OP_CLOSE:
                $conn->close($frame);
                break;
            case Frame::OP_PING:
                $conn->send(new Frame($frame->getPayload(), true, Frame::OP_PONG));
                break;
            case Frame::OP_PONG:
                $pongReceiver = $this->pongReceiver;
                $pongReceiver($frame, $conn);
            break;
        }
    }

    public function enableKeepAlive(LoopInterface $loop, $interval = 30): void {
        $lastPing = new Frame(uniqid(), true, Frame::OP_PING);
        $pingedConnections = new \SplObjectStorage;
        $splClearer = new \SplObjectStorage;

        $this->pongReceiver = function(FrameInterface $frame, $wsConn) use ($pingedConnections, &$lastPing): void {
            if ($frame->getPayload() === $lastPing->getPayload()) {
                $pingedConnections->detach($wsConn);
            }
        };

        $loop->addPeriodicTimer((int) $interval, function() use ($pingedConnections, &$lastPing, $splClearer): void {
            foreach ($pingedConnections as $wsConn) {
                $wsConn->close();
            }
            $pingedConnections->removeAllExcept($splClearer);

            $lastPing = new Frame(uniqid(), true, Frame::OP_PING);

            foreach ($this->connections as $key => $conn) {
                $wsConn = $this->connections[$conn]->connection;

                $wsConn->send($lastPing);
                $pingedConnections->attach($wsConn);
            }
        });
   }
}
