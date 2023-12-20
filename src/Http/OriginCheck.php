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
class OriginCheck implements HttpServerInterface
{
    use CloseResponseTrait;

    /**
     * @param  MessageComponentInterface  $component Component/Application to decorate
     * @param  array  $allowed An array of allowed domains that are allowed to connect from
     */
    public function __construct(
        protected MessageComponentInterface $component,
        public array $allowedOrigins = [],
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection, ?RequestInterface $request = null)
    {
        $header = (string) $request->getHeader('Origin')[0];
        $origin = parse_url($header, PHP_URL_HOST) ?: $header;

        if (! in_array($origin, $this->allowedOrigins)) {
            return $this->close($connection, 403);
        }

        return $this->component->onOpen($connection, $request);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, string $message)
    {
        return $this->component->onMessage($connection, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        return $this->component->onClose($connection);
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $exception)
    {
        return $this->component->onError($connection, $exception);
    }
}
