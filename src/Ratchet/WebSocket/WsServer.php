<?php
namespace Ratchet\WebSocket;
use Ratchet\ComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface as DataComponentInterface;
use Ratchet\Http\HttpServerInterface;
use Ratchet\Http\CloseResponseTrait;
use Psr\Http\Message\RequestInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\RFC6455\Messaging\FrameInterface;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\RFC6455\Messaging\MessageBuffer;
use Ratchet\RFC6455\Messaging\CloseFrameChecker;
use Ratchet\RFC6455\Handshake\ServerNegotiator;
use Ratchet\RFC6455\Handshake\RequestVerifier;
use React\EventLoop\LoopInterface;
use GuzzleHttp\Psr7 as gPsr;

/**
 * The adapter to handle WebSocket requests/responses
 * This is a mediator between the Server and your application to handle real-time messaging through a web browser
 * @link http://ca.php.net/manual/en/ref.http.php
 * @link http://dev.w3.org/html5/websockets/
 */
class WsServer implements HttpServerInterface {
    use CloseResponseTrait;

    /**
     * Decorated component
     * @var \Ratchet\ComponentInterface
     */
    private $delegate;

    /**
     * @var \SplObjectStorage
     */
    protected $connections;

    /**
     * @var \Ratchet\RFC6455\Messaging\CloseFrameChecker
     */
    private $closeFrameChecker;

    /**
     * @var \Ratchet\RFC6455\Handshake\ServerNegotiator
     */
    private $handshakeNegotiator;

    /**
     * @var \Closure
     */
    private $ueFlowFactory;

    /**
     * @var \Closure
     */
    private $pongReceiver;

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
            $this->msgCb = function(ConnectionInterface $conn, MessageInterface $msg) {
                $this->delegate->onMessage($conn, $msg);
            };
        } elseif ($component instanceof DataComponentInterface) {
            $this->msgCb = function(ConnectionInterface $conn, MessageInterface $msg) {
                $this->delegate->onMessage($conn, $msg->getPayload());
            };
        } else {
            throw new \UnexpectedValueException('Expected instance of \Ratchet\WebSocket\MessageComponentInterface or \Ratchet\MessageComponentInterface');
        }

        if (bin2hex('✓') !== 'e29c93') {
            throw new \DomainException('Bad encoding, unicode character ✓ did not match expected value. Ensure charset UTF-8 and check ini val mbstring.func_autoload');
        }

        $this->delegate    = $component;
        $this->connections = new \SplObjectStorage;

        $this->closeFrameChecker   = new CloseFrameChecker;
        $this->handshakeNegotiator = new ServerNegotiator(new RequestVerifier);
        $this->handshakeNegotiator->setStrictSubProtocolCheck(true);

        if ($component instanceof WsServerInterface) {
            $this->handshakeNegotiator->setSupportedSubProtocols($component->getSubProtocols());
        }

        $this->pongReceiver = function() {};

        $reusableUnderflowException = new \UnderflowException;
        $this->ueFlowFactory = function() use ($reusableUnderflowException) {
            return $reusableUnderflowException;
        };
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        if (null === $request) {
            throw new \UnexpectedValueException('$request can not be null');
        }

        $conn->httpRequest = $request;

        $conn->WebSocket            = new \StdClass;
        $conn->WebSocket->closing   = false;

        $response = $this->handshakeNegotiator->handshake($request)->withHeader('X-Powered-By', \Ratchet\VERSION);

        $conn->send(gPsr\str($response));

        if (101 !== $response->getStatusCode()) {
            return $conn->close();
        }

        $wsConn = new WsConnection($conn);

        $streamer = new MessageBuffer(
            $this->closeFrameChecker,
            function(MessageInterface $msg) use ($wsConn) {
                $cb = $this->msgCb;
                $cb($wsConn, $msg);
            },
            function(FrameInterface $frame) use ($wsConn) {
                $this->onControlFrame($frame, $wsConn);
            },
            true,
            $this->ueFlowFactory
        );

        $this->connections->attach($conn, new ConnContext($wsConn, $streamer));

        return $this->delegate->onOpen($wsConn);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        if ($from->WebSocket->closing) {
            return;
        }

        $this->connections[$from]->buffer->onData($msg);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        if ($this->connections->contains($conn)) {
            $context = $this->connections[$conn];
            $this->connections->detach($conn);

            $this->delegate->onClose($context->connection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        if ($this->connections->contains($conn)) {
            $this->delegate->onError($this->connections[$conn]->connection, $e);
        } else {
            $conn->close();
        }
    }

    public function onControlFrame(FrameInterface $frame, WsConnection $conn) {
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

    public function setStrictSubProtocolCheck($enable) {
        $this->handshakeNegotiator->setStrictSubProtocolCheck($enable);
    }

    public function enableKeepAlive(LoopInterface $loop, $interval = 30) {
        $lastPing = new Frame(uniqid(), true, Frame::OP_PING);
        $pingedConnections = new \SplObjectStorage;
        $splClearer = new \SplObjectStorage;

        $this->pongReceiver = function(FrameInterface $frame, $wsConn) use ($pingedConnections, &$lastPing) {
            if ($frame->getPayload() === $lastPing->getPayload()) {
                $pingedConnections->detach($wsConn);
            }
        };

        $loop->addPeriodicTimer((int)$interval, function() use ($pingedConnections, &$lastPing, $splClearer) {
            foreach ($pingedConnections as $wsConn) {
                $wsConn->close();
            }
            $pingedConnections->removeAllExcept($splClearer);

            $lastPing = new Frame(uniqid(), true, Frame::OP_PING);

            foreach ($this->connections as $key => $conn) {
                $wsConn  = $this->connections[$conn]->connection;

                $wsConn->send($lastPing);
                $pingedConnections->attach($wsConn);
            }
        });
   }
}
