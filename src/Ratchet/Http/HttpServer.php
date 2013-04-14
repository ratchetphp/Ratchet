<?php
namespace Ratchet\Http;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

// @todo This class will move to this namespace
use Ratchet\WebSocket\HttpRequestParser;

use Symfony\Component\Routing\RouteCollection;

class HttpServer implements MessageComponentInterface {
    /**
     * Decorated component
     * @var HttpServerInterface
     */
    protected $_decorating;

    protected $_reqParser;

    /**
     * @var Symfony\Component\Routing\RouteCollection
     */
    protected $_routes;

    /**
     * @todo Change parameter from HttpServerInterface to RouteCollection
     */
    public function __construct(HttpServerInterface $component) {
        $this->_decorating = $component;
        $this->_reqParser = new HttpRequestParser;
    }

    /**
     * @{inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
        $conn->Http = new \StdClass;
        $conn->Http->headers = false;
    }

    /**
     * @{inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        if (true !== $from->Http->headers) {
            try {
                if (null === ($request = $this->_reqParser->onMessage($from, $msg))) {
                    return;
                }
            } catch (\OverflowException $oe) {
                return $this->close($from, 413);
            }

            // check routes, return 404 or onOpen the route

            $from->Http->headers = true;
            $from->Http->request = $request;

            return $this->_decorating->onOpen($from, $request);
        }

        $this->_decorating->onMessage($from, $msg);
    }

    /**
     * @{inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        if ($conn->Http->headers) {
            $this->_decorating->onClose($conn);
        }
    }

    /**
     * @{inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        if ($conn->Http->headers) {
            $this->_decorating->onError($conn, $e);
        } else {
            $conn->close();
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
