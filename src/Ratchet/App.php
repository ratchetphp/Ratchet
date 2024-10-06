<?php

namespace Ratchet;
use Ratchet\Http\HttpServer;
use Ratchet\Http\HttpServerInterface;
use Ratchet\Http\OriginCheck;
use Ratchet\Http\Router;
use Ratchet\Server\FlashPolicy;
use Ratchet\Server\IoServer;
use Ratchet\Wamp\WampServer;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\WebSocket\MessageComponentInterface as WsMessageComponentInterface;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\LoopInterface;
use React\Socket\Server as Reactor;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * An opinionated facade class to quickly and easily create a WebSocket server.
 * A few configuration assumptions are made and some best-practice security conventions are applied by default.
 */
class App {

    public RouteCollection $routes;

    public IoServer $flashServer;

    protected IoServer $_server;

    /**
     * @var int
     */
    protected int $_routeCounter = 0;

    public function __construct(
        protected string $httpHost = 'localhost',
        protected int $port = 8080,
        string $address = '127.0.0.1',
        ?LoopInterface $loop = null,
        array $context = []
    ) {
        if (extension_loaded('xdebug') && getenv('RATCHET_DISABLE_XDEBUG_WARN') === false) {
            trigger_error('XDebug extension detected. Remember to disable this if performance testing or going live!', E_USER_WARNING);
        }

        if (null === $loop) {
            $loop = LoopFactory::create();
        }

        $socket = new Reactor($address . ':' . $this->port, $loop, $context);

        $this->routes = new RouteCollection;
        $this->_server = new IoServer(new HttpServer(new Router(new UrlMatcher($this->routes, new RequestContext))), $socket, $loop);

        $policy = new FlashPolicy;
        $policy->addAllowedAccess($this->httpHost, 80);
        $policy->addAllowedAccess($this->httpHost, $this->port);

        if (80 == $this->port) {
            $flashUri = '0.0.0.0:843';
        } else {
            $flashUri = 8843;
        }
        $flashSock = new Reactor($flashUri, $loop);
        $this->flashServer = new IoServer($policy, $flashSock);
    }

    /**
     * Add an endpoint/application to the server
     * @param string $path The URI the client will connect to
     * @param ComponentInterface $controller Your application to server for the route. If not specified, assumed to be for a WebSocket
     * @param array              $allowedOrigins An array of hosts allowed to connect (same host by default), ['*'] for any
     * @param string|null $httpHost Override the $httpHost variable provided in the __construct
     * @return ComponentInterface|WsServer
     */
    public function route(
        string $path,
        ComponentInterface $controller,
        array $allowedOrigins = [],
        ?string $httpHost = null): WsServer|OriginCheck|ComponentInterface {
        if ($controller instanceof HttpServerInterface || $controller instanceof WsServer) {
            $decorated = $controller;
        } elseif ($controller instanceof WampServerInterface) {
            $decorated = new WsServer(new WampServer($controller));
            $decorated->enableKeepAlive($this->_server->loop);
        } elseif ($controller instanceof MessageComponentInterface || $controller instanceof WsMessageComponentInterface) {
            $decorated = new WsServer($controller);
            $decorated->enableKeepAlive($this->_server->loop);
        } else {
            $decorated = $controller;
        }

        if ($httpHost === null) {
            $httpHost = $this->httpHost;
        }

        $allowedOrigins = array_values($allowedOrigins);
        if (0 === count($allowedOrigins)) {
            $allowedOrigins[] = $httpHost;
        }
        if ('*' !== $allowedOrigins[0]) {
            $decorated = new OriginCheck($decorated, $allowedOrigins);
        }

        //allow origins in flash policy server
        if(empty($this->flashServer) === false) {
            foreach($allowedOrigins as $allowedOrigin) {
                $this->flashServer->app->addAllowedAccess($allowedOrigin, $this->port);
            }
        }

        $this->routes->add('rr-' . ++$this->_routeCounter, new Route($path, [
            '_controller' => $decorated,
        ], [
            'Origin' => $this->httpHost,
        ], [], $httpHost, [], ['GET']));

        return $decorated;
    }

    /**
     * Run the server by entering the event loop
     */
    public function run(): void {
        $this->_server->run();
    }
}
