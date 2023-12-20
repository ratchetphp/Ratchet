<?php

namespace Ratchet\Http;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class HttpServer implements MessageComponentInterface
{
    use CloseResponseTrait;

    /**
     * Buffers incoming HTTP requests returning a Guzzle Request when coalesced
     *
     * @note May not expose this in the future, may do through facade methods
     */
    protected HttpRequestParser $requestParser;

    public function __construct(
        protected HttpServerInterface $httpServer,
    ) {
        $this->requestParser = new HttpRequestParser;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection)
    {
        $connection->httpHeadersReceived = false;
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, string $message)
    {
        if ($connection->httpHeadersReceived !== true) {
            try {
                if (null === ($request = $this->requestParser->onMessage($connection, $message))) {
                    return;
                }
            } catch (\OverflowException $exception) {
                return $this->close($connection, 413);
            }

            $connection->httpHeadersReceived = true;

            return $this->httpServer->onOpen($connection, $request);
        }

        $this->httpServer->onMessage($connection, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        if ($connection->httpHeadersReceived) {
            $this->httpServer->onClose($connection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $exception)
    {
        if ($connection->httpHeadersReceived) {
            $this->httpServer->onError($connection, $exception);
        } else {
            $this->close($connection, 500);
        }
    }
}
