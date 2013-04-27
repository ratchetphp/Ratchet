<?php
namespace Ratchet\Http;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;

/**
 */
class RoutedHttpServer implements MessageComponentInterface {
    protected $_routes;
    protected $_server;

    public function __construct(RouteCollection $routes = null) {
        if (null == $routes) {
            $routes = new RouteCollection;
        }

        $this->_routes = $routes;
        $this->_server = new HttpServer(new Router(new UrlMatcher($routes, new RequestContext)));
    }

    public function addRoute($path, MessageComponentInterface $controller) {
        $this->_routes->add(uniqid(), new Route($path, array(
            '_controller' => new WsServer($controller)
        )));
    }

    public function addHttpRoute($path, HttpServerInterface $controller) {
        $this->_routes->add(uniqid(), new Route($path, array(
            '_controller' => $controller
        )));
    }

    /**
     * {@inheritdoc}
     */
    function onOpen(ConnectionInterface $conn) {
        $this->_server->onOpen($conn);
    }

    /**
     * {@inheritdoc}
     */
    function onMessage(ConnectionInterface $from, $msg) {
        $this->_server->onMessage($from, $msg);
    }

    /**
     * {@inheritdoc}
     */
    function onClose(ConnectionInterface $conn) {
        $this->_server->onClose($conn);
    }

    /**
     * {@inheritdoc}
     */
    function onError(ConnectionInterface $conn, \Exception $e) {
        $this->_server->onError($conn, $e);
    }
}