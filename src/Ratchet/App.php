<?php
namespace Ratchet;
use React\EventLoop\LoopInterface;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as Reactor;
use Ratchet\Http\HttpServerInterface;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\Server\IoServer;
use Ratchet\Server\FlashPolicy;
use Ratchet\Http\HttpServer;
use Ratchet\Http\Router;
use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\WampServer;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class App {
    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    public $routes;

    /**
     * @var \Ratchet\Server\IoServer
     */
    public $flashServer;

    /**
     * @var \Ratchet\Server\IoServer
     */
    protected $_server;

    /**
     * The Host passed in construct used for same origin policy
     * @var string
     */
    protected $httpHost;

    protected $_routeCounter = 0;

    /**
     * @param string        $httpHost
     * @param int           $port
     * @param string        $address
     * @param LoopInterface $loop
     */
    public function __construct($httpHost = 'localhost', $port = 8080, $address = '127.0.0.1', LoopInterface $loop = null) {
        if (extension_loaded('xdebug')) {
            echo "XDebug extension detected. Remember to disable this if performance testing or going live!\n";
        }

        if (null === $loop) {
            $loop = LoopFactory::create();
        }

        $this->httpHost = $httpHost;

        $socket = new Reactor($loop);
        $socket->listen($port, $address);

        $this->routes  = new RouteCollection;
        $this->_server = new IoServer(new HttpServer(new Router(new UrlMatcher($this->routes, new RequestContext))), $socket, $loop);

        $policy = new FlashPolicy;
        $policy->addAllowedAccess($httpHost, 80);
        $policy->addAllowedAccess($httpHost, $port);
        $flashSock = new Reactor($loop);
        $this->flashServer = new IoServer($policy, $flashSock);

        if (80 == $port) {
            $flashSock->listen(843, '0.0.0.0');
        } else {
            $flashSock->listen(8843);
        }
    }

    /**
     * @param string             $path
     * @param ComponentInterface $controller
     * @return \Symfony\Component\Routing\Route
     */
    public function route($path, ComponentInterface $controller) {
        if ($controller instanceof HttpServerInterface || $controller instanceof WsServer) {
            $decorated = $controller;
        } elseif ($controller instanceof WampServerInterface) {
            $decorated = new WsServer(new WampServer($controller));
        } elseif ($controller instanceof MessageComponentInterface) {
            $decorated = new WsServer($controller);
        } else {
            $decorated = $controller;
        }

        $this->routes->add('rr-' . ++$this->_routeCounter, new Route($path, array('_controller' => $decorated), array(), array(), $this->httpHost));

        return $decorated;
    }

    public function run() {
        $this->_server->run();
    }
}