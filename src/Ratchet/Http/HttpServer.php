<?php

namespace Ratchet\Http;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class HttpServer implements MessageComponentInterface {
    use CloseResponseTrait;

    /**
     * Buffers incoming HTTP requests returning a Guzzle Request when coalesced
     * @note May not expose this in the future, may do through facade methods
     */
    protected \Ratchet\Http\HttpRequestParser $_reqParser;

    /**
     * @param HttpServerInterface
     */
    public function __construct(
        protected \Ratchet\Http\HttpServerInterface $_httpServer
    ) {
        $this->_reqParser = new HttpRequestParser;
    }

    #[\Override]
    public function onOpen(ConnectionInterface $conn) {
        $conn->httpHeadersReceived = false;
    }

    #[\Override]
    public function onMessage(ConnectionInterface $from, $msg) {
        if (true !== $from->httpHeadersReceived) {
            try {
                if (null === ($request = $this->_reqParser->onMessage($from, $msg))) {
                    return;
                }
            } catch (\OverflowException) {
                return $this->close($from, 413);
            }

            $from->httpHeadersReceived = true;

            return $this->_httpServer->onOpen($from, $request);
        }

        $this->_httpServer->onMessage($from, $msg);
    }

    #[\Override]
    public function onClose(ConnectionInterface $conn) {
        if ($conn->httpHeadersReceived) {
            $this->_httpServer->onClose($conn);
        }
    }

    #[\Override]
    public function onError(ConnectionInterface $conn, \Exception $e) {
        if ($conn->httpHeadersReceived) {
            $this->_httpServer->onError($conn, $e);
        } else {
            $this->close($conn, 500);
        }
    }
}
