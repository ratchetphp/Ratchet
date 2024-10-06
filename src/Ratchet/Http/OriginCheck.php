<?php

namespace Ratchet\Http;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * A middleware to ensure JavaScript clients connecting are from the expected domain.
 * This protects other websites from open WebSocket connections to your application.
 * Note: This can be spoofed from non-web browser clients
 */
class OriginCheck implements HttpServerInterface {
    use CloseResponseTrait;

    public $allowedOrigins = [];

    /**
     * @param MessageComponentInterface $_component Component/Application to decorate
     * @param array                     $allowed   An array of allowed domains that are allowed to connect from
     */
    public function __construct(
        protected \Ratchet\MessageComponentInterface $_component,
        array $allowed = []
    ) {
        $this->allowedOrigins += $allowed;
    }

    #[\Override]
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        $header = $request->getHeader('Origin')[0];
        $origin = parse_url($header, PHP_URL_HOST) ?: $header;

        if (! in_array($origin, $this->allowedOrigins)) {
            return $this->close($conn, 403);
        }

        return $this->_component->onOpen($conn, $request);
    }

    #[\Override]
    function onMessage(ConnectionInterface $from, $msg) {
        return $this->_component->onMessage($from, $msg);
    }

    #[\Override]
    function onClose(ConnectionInterface $conn) {
        return $this->_component->onClose($conn);
    }

    #[\Override]
    function onError(ConnectionInterface $conn, \Exception $e) {
        return $this->_component->onError($conn, $e);
    }
}