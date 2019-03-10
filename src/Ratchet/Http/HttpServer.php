<?php
namespace Ratchet\Http;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class HttpServer implements MessageComponentInterface {
    use CloseResponseTrait;

    /**
     * Buffers incoming HTTP requests returning a Guzzle Request when coalesced
     * @var HttpRequestParser
     * @note May not expose this in the future, may do through facade methods
     */
    protected $_reqParser;

    /**
     * @var \Ratchet\Http\HttpServerInterface
     */
    protected $_httpServer;

    /**
     * @var HttpConnection[]
     */
    private $connections;

    /**
     * @param HttpServerInterface
     */
    public function __construct(HttpServerInterface $component) {
        $this->_httpServer = $component;
        $this->_reqParser  = new HttpRequestParser;
        $this->connections = new \SplObjectStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
        $this->connections->attach($conn, new HttpConnection($conn, new NoOpHttpServerController));
        $conn->httpHeadersReceived = false; // @deprecated
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        $httpConn = $this->connections[$from];

//        if (true !== $from->get('HTTP.headersReceived')) {
        if (!$httpConn->has('HTTP.request')) {
            try {
                if (null === ($request = $this->_reqParser->onMessage($httpConn, $msg))) {
                    return;
                }
            } catch (\OverflowException $oe) {
                return $this->close($from, 413);
            }

            $from->httpHeadersReceived = true; // @deprecated
            $httpConn->receivedHttpHeaders($request);

            return $this->_httpServer->onOpen($httpConn, $request);
        }

        $this->_httpServer->onMessage($httpConn, $msg);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        $httpConn = $this->connections[$conn];

        $this->connections->detach($conn);

        if ($httpConn->has('HTTP.request')) {
            $this->_httpServer->onClose($httpConn);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        if ($conn->has('HTTP.request')) {
            $this->_httpServer->onError($conn, $e);
        } else {
            $this->close($conn, 500);
        }
    }
}
