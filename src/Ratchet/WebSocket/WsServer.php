<?php
namespace Ratchet\WebSocket;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Ratchet\WebSocket\Version;
use Ratchet\WebSocket\Encoding\ToggleableValidator;

/**
 * The adapter to handle WebSocket requests/responses
 * This is a mediator between the Server and your application to handle real-time messaging through a web browser
 * @link http://ca.php.net/manual/en/ref.http.php
 * @link http://dev.w3.org/html5/websockets/
 */
class WsServer implements HttpServerInterface {
    /**
     * Manage the various WebSocket versions to support
     * @var VersionManager
     * @note May not expose this in the future, may do through facade methods
     */
    public $versioner;

    /**
     * Decorated component
     * @var \Ratchet\MessageComponentInterface
     */
    protected $_decorating;

    /**
     * @var \SplObjectStorage
     */
    protected $connections;

    /**
     * For now, array_push accepted subprotocols to this array
     * @deprecated
     * @temporary
     */
    protected $acceptedSubProtocols = array();

    /**
     * UTF-8 validator
     * @var \Ratchet\WebSocket\Encoding\ValidatorInterface
     */
    protected $validator;

    /**
     * Flag if we have checked the decorated component for sub-protocols
     * @var boolean
     */
    private $isSpGenerated = false;

    /**
     * @param \Ratchet\MessageComponentInterface $component Your application to run with WebSockets
     * If you want to enable sub-protocols have your component implement WsServerInterface as well
     */
    public function __construct(MessageComponentInterface $component) {
        $this->versioner = new VersionManager;
        $this->validator = new ToggleableValidator;

        $this->versioner
            ->enableVersion(new Version\RFC6455($this->validator))
            ->enableVersion(new Version\HyBi10($this->validator))
            ->enableVersion(new Version\Hixie76)
        ;

        $this->_decorating = $component;
        $this->connections = new \SplObjectStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn, RequestInterface $headers = null) {
        $conn->WebSocket              = new \StdClass;
        $conn->WebSocket->request     = $headers;
        $conn->WebSocket->established = false;

        $this->attemptUpgrade($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        if (true === $from->WebSocket->established) {
            return $from->WebSocket->version->onMessage($this->connections[$from], $msg);
        }

        $this->attemptUpgrade($from, $msg);
    }

    protected function attemptUpgrade(ConnectionInterface $conn, $data = '') {
        if ('' !== $data) {
            $conn->WebSocket->request->getBody()->write($data);
        } else {
            if (!$this->versioner->isVersionEnabled($conn->WebSocket->request)) {
                return $this->close($conn);
            }

            $conn->WebSocket->request = $conn->WebSocket->request;
            $conn->WebSocket->version = $this->versioner->getVersion($conn->WebSocket->request);
        }

        try {
            $response = $conn->WebSocket->version->handshake($conn->WebSocket->request);
        } catch (\UnderflowException $e) {
            return;
        }

        // This needs to be refactored later on, incorporated with routing
        if ('' !== ($agreedSubProtocols = $this->getSubProtocolString($conn->WebSocket->request->getTokenizedHeader('Sec-WebSocket-Protocol', ',')))) {
            $response->setHeader('Sec-WebSocket-Protocol', $agreedSubProtocols);
        }

        $response->setHeader('X-Powered-By', \Ratchet\VERSION);
        $conn->send((string)$response);

        if (101 != $response->getStatusCode()) {
            return $conn->close();
        }

        $upgraded = $conn->WebSocket->version->upgradeConnection($conn, $this->_decorating);

        $this->connections->attach($conn, $upgraded);

        $upgraded->WebSocket->established = true;

        return $this->_decorating->onOpen($upgraded);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        if ($this->connections->contains($conn)) {
            $decor = $this->connections[$conn];
            $this->connections->detach($conn);

            $this->_decorating->onClose($decor);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        if ($conn->WebSocket->established) {
            $this->_decorating->onError($this->connections[$conn], $e);
        } else {
            $conn->close();
        }
    }

    /**
     * Disable a specific version of the WebSocket protocol
     * @param int $versionId Version ID to disable
     * @return WsServer
     */
    public function disableVersion($versionId) {
        $this->versioner->disableVersion($versionId);

        return $this;
    }

    /**
     * Toggle weather to check encoding of incoming messages
     * @param bool
     * @return WsServer
     */
    public function setEncodingChecks($opt) {
        $this->validator->on = (boolean)$opt;

        return $this;
    }

    /**
     * @param string
     * @return boolean
     */
    public function isSubProtocolSupported($name) {
        if (!$this->isSpGenerated) {
            if ($this->_decorating instanceof WsServerInterface) {
                $this->acceptedSubProtocols = array_flip($this->_decorating->getSubProtocols());
            }

            $this->isSpGenerated = true;
        }

        return array_key_exists($name, $this->acceptedSubProtocols);
    }

    /**
     * @param  \Traversable|null $requested
     * @return string
     */
    protected function getSubProtocolString(\Traversable $requested = null) {
        if (null === $requested) {
            return '';
        }

        $result = array();

        foreach ($requested as $sub) {
            if ($this->isSubProtocolSupported($sub)) {
                $result[] = $sub;
            }
        }

        return implode(',', $result);
    }

    /**
     * Close a connection with an HTTP response
     * @param \Ratchet\ConnectionInterface $conn
     * @param int                          $code HTTP status code
     * @return void
     */
    protected function close(ConnectionInterface $conn, $code = 400) {
        $response = new Response($code, array(
            'Sec-WebSocket-Version' => $this->versioner->getSupportedVersionString()
          , 'X-Powered-By'          => \Ratchet\VERSION
        ));

        $conn->send((string)$response);
        $conn->close();
    }
}