<?php

namespace Ratchet\Http;

use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

class Router implements HttpServerInterface
{
    use CloseResponseTrait;

    protected UrlMatcherInterface $matcher;

    private NoOpHttpServerController $noopController;

    public function __construct(UrlMatcherInterface $matcher)
    {
        $this->matcher = $matcher;
        $this->noopController = new NoOpHttpServerController;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \UnexpectedValueException If a controller is not \Ratchet\Http\HttpServerInterface
     */
    public function onOpen(ConnectionInterface $connection, ?RequestInterface $request = null)
    {
        if ($request === null) {
            throw new \UnexpectedValueException('$request can not be null');
        }

        $connection->controller = $this->noopController;

        $uri = $request->getUri();

        $context = $this->matcher->getContext();
        $context->setMethod($request->getMethod());
        $context->setHost($uri->getHost());

        try {
            $route = $this->matcher->match($uri->getPath());
        } catch (MethodNotAllowedException $nae) {
            return $this->close($connection, 405, ['Allow' => $nae->getAllowedMethods()]);
        } catch (ResourceNotFoundException $nfe) {
            return $this->close($connection, 404);
        }

        if (is_string($route['_controller']) && class_exists($route['_controller'])) {
            $route['_controller'] = new $route['_controller'];
        }

        if (! ($route['_controller'] instanceof HttpServerInterface)) {
            throw new \UnexpectedValueException('All routes must implement Ratchet\Http\HttpServerInterface');
        }

        $parameters = [];
        foreach ($route as $key => $value) {
            if ((is_string($key)) && (substr($key, 0, 1) !== '_')) {
                $parameters[$key] = $value;
            }
        }
        $parameters = array_merge($parameters, Query::parse($uri->getQuery() ?: ''));

        $request = $request->withUri($uri->withQuery(Query::build($parameters)));

        $connection->controller = $route['_controller'];
        $connection->controller->onOpen($connection, $request);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, string $message)
    {
        $connection->controller->onMessage($connection, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        if (isset($connection->controller)) {
            $connection->controller->onClose($connection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $exception)
    {
        if (isset($connection->controller)) {
            $connection->controller->onError($connection, $exception);
        }
    }
}
