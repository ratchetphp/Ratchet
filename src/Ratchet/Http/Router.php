<?php
namespace Ratchet\Http;
use Ratchet\ConnectionInterface;
use Guzzle\Http\Message\RequestInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class Router implements HttpServerInterface {
    /**
     * @var \Symfony\Component\Routing\Matcher\UrlMatcherInterface
     */
    protected $_matcher;

    public function __construct(UrlMatcherInterface $matcher) {
        $this->_matcher = $matcher;
    }

    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        try {
            $route = $this->_matcher->match($request->getPath());
        } catch (MethodNotAllowedException $nae) {
            return $this->close($from, 403);
        } catch (ResourceNotFoundException $nfe) {
            return $this->close($from, 404);
        }

        if (is_string($route['_controller']) && class_exists($route['_controller'])) {
            $route['_controller'] = new $route['_controller'];
        }

        if (!($route['_controller'] instanceof HttpServerInterface)) {
            throw new \UnexpectedValueException('All routes must implement Ratchet\HttpServerInterface');
        }

        $conn->controller = $route['_controller'];

        $conn->controller->onOpen($conn, $request);
    }

    /**
     * @{inheritdoc}
     */
    function onMessage(ConnectionInterface $from, $msg) {
        $from->controller->onMessage($from, $msg);
    }

    /**
     * @{inheritdoc}
     */
    function onClose(ConnectionInterface $conn) {
        $conn->controller->onClose($conn);
    }

    /**
     * @{inheritdoc}
     */
    function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->controller->onError($conn, $e);
    }
}