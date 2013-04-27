<?php
namespace Ratchet\Http;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Guzzle\Http\Message\Response;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class HttpServer implements MessageComponentInterface {
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
     * @param HttpServerInterface
     */
    public function __construct(HttpServerInterface $server) {
        $this->_httpServer = $server;
        $this->_reqParser  = new HttpRequestParser;
    }

    /**
     * @{inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
        $conn->httpHeadersReceived = false;
    }

    /**
     * @{inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        if (true !== $from->httpHeadersReceived) {
            try {
                if (null === ($request = $this->_reqParser->onMessage($from, $msg))) {
                    return;
                }
            } catch (\OverflowException $oe) {
                return $this->close($from, 413);
            }

            $from->httpHeadersReceived = true;

            return $this->_httpServer->onOpen($from, $request);
        }

        $this->_httpServer->onMessage($from, $msg);
    }

    /**
     * @{inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        if ($conn->httpHeadersReceived) {
            $this->_httpServer->onClose($conn);
        }
    }

    /**
     * @{inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        if ($conn->httpHeadersReceived) {
            $this->_httpServer->onError($conn, $e);
        } else {
            $this->close($conn, 500);
        }
    }

    /**
     * Close a connection with an HTTP response
     * @param \Ratchet\ConnectionInterface $conn
     * @param int                          $code HTTP status code
     * @return void
     */
    protected function close(ConnectionInterface $conn, $code = 400) {
        $response = new Response($code, array(
            'X-Powered-By' => \Ratchet\VERSION
        ));

        $conn->send((string)$response);
        $conn->close();
    }
}
