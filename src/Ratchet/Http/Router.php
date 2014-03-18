<?php
namespace Ratchet\Http;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Ratchet\ConnectionInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

class Router implements HttpServerInterface
{
    /**
     * @var \Symfony\Component\Routing\Matcher\UrlMatcherInterface
     */
    protected $_matcher;

    public function __construct(UrlMatcherInterface $matcher) {
        $this->_matcher = $matcher;
    }

    /**
     * {@inheritdoc}
     * @throws \UnexpectedValueException If a controller is not \Ratchet\Http\HttpServerInterface
     */
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        if (null === $request) {
            throw new \UnexpectedValueException('$request can not be null');
        }

        $context = $this->_matcher->getContext();
        $context->setMethod($request->getMethod());
        $context->setHost($request->getHost());

        try {
            $route = $this->_matcher->match($request->getPath());
        } catch (MethodNotAllowedException $nae) {
            return $this->close($conn, 403);
        } catch (ResourceNotFoundException $nfe) {
            return $this->close($conn, 404);
        }

        if (is_string($route['_controller']) && class_exists($route['_controller'])) {
            $route['_controller'] = new $route['_controller'];
        }

        if (!($route['_controller'] instanceof HttpServerInterface)) {
            throw new \UnexpectedValueException('All routes must implement Ratchet\Http\HttpServerInterface');
        }

        $conn->parameters = $this->extractParameters($route['_route'], $request);
        $conn->controller = $route['_controller'];
        $conn->controller->onOpen($conn, $request);
    }

    /**
     * @param string                                $route
     * @param \Guzzle\Http\Message\RequestInterface $request
     *
     * @return array
     */
    protected function extractParameters($route, RequestInterface $request) {
        /** @var $routes Route[] */
        $routes   = $this->routes->getIterator();
        $compiled = $routes[$route]->compile();
        preg_match($compiled->getRegex(), $request->getPath(), $matches);

        return array_intersect_key($matches, array_flip($compiled->getVariables()));
    }

    /**
     * {@inheritdoc}
     */
    function onMessage(ConnectionInterface $from, $msg) {
        $from->controller->onMessage($from, $msg);
    }

    /**
     * {@inheritdoc}
     */
    function onClose(ConnectionInterface $conn) {
        if (isset($conn->controller)) {
            $conn->controller->onClose($conn);
        }
    }

    /**
     * {@inheritdoc}
     */
    function onError(ConnectionInterface $conn, \Exception $e) {
        if (isset($conn->controller)) {
            $conn->controller->onError($conn, $e);
        }
    }

    /**
     * Close a connection with an HTTP response
     * @param \Ratchet\ConnectionInterface $conn
     * @param int                          $code HTTP status code
     * @return null
     */
    protected function close(ConnectionInterface $conn, $code = 400) {
        $response = new Response($code, array(
            'X-Powered-By' => \Ratchet\VERSION
        ));

        $conn->send((string)$response);
        $conn->close();
    }
}
