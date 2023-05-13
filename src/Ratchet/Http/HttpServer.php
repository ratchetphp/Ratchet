<?php
namespace Ratchet\Http;
use GuzzleHttp\Psr7\Request;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class HttpServer implements MessageComponentInterface {
    use CloseResponseTrait;

    /**
     * Buffers incoming HTTP requests returning a Guzzle Request when coalesced
     * @var HttpRequestParser
     * @note May not expose this in the future, may do through facade methods
     */
    protected $_reqParser;

    /**
     * @var \Ratchet\Http\HttpServerInterface
     */
    protected $_httpServer;

    /**
     * Storage for dynamic properties.
     *
     * @var array
     */
    protected $_properties = [];

    /**
     * @param HttpServerInterface
     */
    public function __construct(HttpServerInterface $component) {
        $this->_httpServer = $component;
        $this->_reqParser  = new HttpRequestParser;
    }

    /**
     * Allow setting dynamic properties.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function __set($key, $value) {
        if (property_exists($this, $key)) {
            $this->_properties[$key] = $value;
        }
    }

    /**
     * Get a property that has been declared dynamically
     *
     * @param string $key
     *
     * @return mixed|void
     */
    public function __get($key) {
        if (isset($this->_properties[$key])) {
            return $this->_properties[$key];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
        $conn->httpHeadersReceived = false;
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        if (true !== $from->httpHeadersReceived) {
            try {
                if (null === ($request = $this->_reqParser->onMessage($from, $msg))) {
                    return;
                }
            } catch (\OverflowException $oe) {
                return $this->close($from, 413);
            }

            $from->httpHeadersReceived = true;

            return $this->_httpServer->onOpen($from, $request);
        }

        $this->_httpServer->onMessage($from, $msg);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        if ($conn->httpHeadersReceived) {
            $this->_httpServer->onClose($conn);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        if ($conn->httpHeadersReceived) {
            $this->_httpServer->onError($conn, $e);
        } else {
            $this->close($conn, 500);
        }
    }
}
