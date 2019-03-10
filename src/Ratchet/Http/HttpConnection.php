<?php
namespace Ratchet\Http;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionDecorator;
use Ratchet\ConnectionInterface;

final class HttpConnection implements ConnectionInterface {
    use ConnectionDecorator {
        ConnectionDecorator::__construct as __decorator;
    }

    public function __construct(ConnectionInterface $conn, HttpServerInterface $defaultController) {
        $this->__decorator($conn, [
            'HTTP.controller' => $defaultController
        ]);
    }

    public function receivedHttpHeaders(RequestInterface $request) {
        if ($this->has('HTTP.request')) {
            throw new \RuntimeException('HTTP request already received');
        }

        $this->properties['HTTP.request'] = $request;
    }

    public function setController(HttpServerInterface $controller) {
//        if ($this->has('HTTP.controller')) {
//            throw new \RuntimeException('Controller already set for this connection');
//        }

        $this->properties['HTTP.controller'] = $controller;
    }

    public function send($data) {
        return $this->connection->send($data);
    }
}
