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
    public $component;

    /**
     * @var \SplObjectStorage
     */
    protected $connections;

    /**
     * Holder of accepted protocols, implement through WampServerInterface
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

        $this->component   = $component;
        $this->connections = new \SplObjectStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        if (null === $request) {
            throw new \UnexpectedValueException('$request can not be null');
        }

        $conn->WebSocket              = new \StdClass;
        $conn->WebSocket->request     = $request;
        $conn->WebSocket->established = false;
        $conn->WebSocket->closing     = false;

        $this->attemptUpgrade($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        if ($from->WebSocket->closing) {
            return;
        }

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

            $conn->WebSocket->version = $this->versioner->getVersion($conn->WebSocket->request);
        }

        try {
            $response = $conn->WebSocket->version->handshake($conn->WebSocket->request);
        } catch (\UnderflowException $e) {
            return;
        }

        if (null !== ($subHeader = $conn->WebSocket->request->getHeader('Sec-WebSocket-Protocol'))) {
            if ('' !== ($agreedSubProtocols = $this->getSubProtocolString($subHeader->normalize()))) {
                $response->setHeader('Sec-WebSocket-Protocol', $agreedSubProtocols);
            }
        }

        $response->setHeader('X-Powered-By', \Ratchet\VERSION);
        $conn->send((string)$response);

        if (101 != $response->getStatusCode()) {
            return $conn->close();
        }

        $upgraded = $conn->WebSocket->version->upgradeConnection($conn, $this->component);

        $this->connections->attach($conn, $upgraded);

        $upgraded->WebSocket->established = true;

        return $this->component->onOpen($upgraded);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        if ($this->connections->contains($conn)) {
            $decor = $this->connections[$conn];
            $this->connections->detach($conn);

            $this->component->onClose($decor);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        if ($conn->WebSocket->established && $this->connections->contains($conn)) {
            $this->component->onError($this->connections[$conn], $e);
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
            if ($this->component instanceof WsServerInterface) {
                $this->acceptedSubProtocols = array_flip($this->component->getSubProtocols());
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
        if (null !== $requested) {
            foreach ($requested as $sub) {
                if ($this->isSubProtocolSupported($sub)) {
                    return $sub;
                }
            }
        }

        return '';
    }

    /**
     * Close a connection with an HTTP response
     * @param \Ratchet\ConnectionInterface $conn
     * @param int                          $code HTTP status code
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
