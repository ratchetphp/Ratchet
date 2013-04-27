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
     * @var \Symfony\Component\Routing\RouteCollection A collection with \Ratchet\MessageComponentInterface controllers
     */
    protected $_routes;

    /**
     * @param string          $host
     * @param RouteCollection $collection
     * @throws \UnexpectedValueException If a Route Controller does not map to a \Ratchet\MessageComponentInterface
     */
    public function __construct($host, RouteCollection $collection = null) {
        if (null === $collection) {
            $collection = new RouteCollection;
        } else {
            foreach ($collection as $routeName => $route) {
                if (is_string($route['_controller']) && class_exists($route['_controller'])) {
                    $route['_controller'] = new $route['_controller'];
                }

                if (!($route['_controller'] instanceof HttpServerInterface)) {
                    throw new \UnexpectedValueException('All routes must implement Ratchet\MessageComponentInterface');
                }
            }
        }

        $collection->setHost($host);

        $this->_routes    = $collection;
        $this->_reqParser = new HttpRequestParser;
    }

    /**
     * @param string
     * @param string
     * @param Ratchet\Http\HttpServerInterface
     * @param array
     */
    public function addRoute($name, $path, HttpServerInterface $controller) {
        $this->_routes->add($name, new Route($path, array(
            '_controller' => $controller
        )));
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

            $context = new RequestContext($request->getUrl(), $request->getMethod(), $request->getHost(), $request->getScheme(), $request->getPort());
            $matcher = new UrlMatcher($this->_routes, $context);

            try {
                $route = $matcher->match($request->getPath());
            } catch (MethodNotAllowedException $nae) {
                return $this->close($from, 403);
            } catch (ResourceNotFoundException $nfe) {
                return $this->close($from, 404);
            }

            $from->Http->headers    = true;
            $from->Http->controller = $route['_controller'];

            return $from->Http->controller->onOpen($from, $request);
        }

        $from->Http->controller->onMessage($from, $msg);
    }

    /**
     * @{inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        if ($conn->Http->headers) {
            $conn->Http->controller->onClose($conn);
        }
    }

    /**
     * @{inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        if ($conn->Http->headers) {
            $conn->Http->controller->onError($conn, $e);
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
