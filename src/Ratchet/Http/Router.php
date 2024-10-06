<?php

namespace Ratchet\Http;
use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class Router implements HttpServerInterface {
    use CloseResponseTrait;

    private NoOpHttpServerController $_noopController;

    public function __construct(
        protected \Symfony\Component\Routing\Matcher\UrlMatcherInterface $_matcher
    ) {
        $this->_noopController = new NoOpHttpServerController;
    }

    /**
     * @throws \UnexpectedValueException If a controller is not \Ratchet\Http\HttpServerInterface
     */
    #[\Override]
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        if (null === $request) {
            throw new \UnexpectedValueException('$request can not be null');
        }

        $conn->controller = $this->_noopController;

        $uri = $request->getUri();

        $context = $this->_matcher->getContext();
        $context->setMethod($request->getMethod());
        $context->setHost($uri->getHost());

        try {
            $route = $this->_matcher->match($uri->getPath());
        } catch (MethodNotAllowedException $nae) {
            return $this->close($conn, 405, [
                'Allow' => $nae->getAllowedMethods(),
            ]);
        } catch (ResourceNotFoundException) {
            return $this->close($conn, 404);
        }

        if (is_string($route['_controller']) && class_exists($route['_controller'])) {
            $route['_controller'] = new $route['_controller'];
        }

        if (! ($route['_controller'] instanceof HttpServerInterface)) {
            throw new \UnexpectedValueException('All routes must implement Ratchet\Http\HttpServerInterface');
        }

        $parameters = [];
        foreach($route as $key => $value) {
            if ((is_string($key)) && (! str_starts_with($key, '_'))) {
                $parameters[$key] = $value;
            }
        }
        $parameters = array_merge($parameters, Query::parse($uri->getQuery() ?: ''));

        $request = $request->withUri($uri->withQuery(Query::build($parameters)));

        $conn->controller = $route['_controller'];
        $conn->controller->onOpen($conn, $request);
    }

    #[\Override]
    public function onMessage(ConnectionInterface $from, $msg) {
        $from->controller->onMessage($from, $msg);
    }

    #[\Override]
    public function onClose(ConnectionInterface $conn) {
        if (isset($conn->controller)) {
            $conn->controller->onClose($conn);
        }
    }

    #[\Override]
    public function onError(ConnectionInterface $conn, \Exception $e) {
        if (isset($conn->controller)) {
            $conn->controller->onError($conn, $e);
        }
    }
}
