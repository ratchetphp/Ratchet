<?php
namespace Ratchet\WebSocket;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\Version;
use Guzzle\Http\Message\Response;

/**
 * The adapter to handle WebSocket requests/responses
 * This is a mediator between the Server and your application to handle real-time messaging through a web browser
 * @link http://ca.php.net/manual/en/ref.http.php
 * @link http://dev.w3.org/html5/websockets/
 */
class WsServer implements MessageComponentInterface {
    /**
     * Buffers incoming HTTP requests returning a Guzzle Request when coalesced
     * @var HttpRequestParser
     * @note May not expose this in the future, may do through facade methods
     */
    public $reqParser;

    /**
     * Manage the various WebSocket versions to support
     * @var VersionManager
     */
    protected $versioner;

    /**
     * Decorated component
     * @var Ratchet\MessageComponentInterface|WsServerInterface
     */
    protected $_decorating;

    /**
     * @var SplObjectStorage
     */
    protected $connections;

    /**
     * For now, array_push accepted subprotocols to this array
     * @deprecated
     * @temporary
     */
    protected $acceptedSubProtocols = array();

    /**
     * Flag if we have checked the decorated component for sub-protocols
     * @var boolean
     */
    private $isSpGenerated = false;

    /**
     * @param Ratchet\MessageComponentInterface Your application to run with WebSockets
     */
    public function __construct(MessageComponentInterface $component) {
        //mb_internal_encoding('UTF-8');

        $this->reqParser = new HttpRequestParser;
        $this->versioner = new VersionManager;

        $this->versioner
            ->enableVersion(new Version\RFC6455($component))
            ->enableVersion(new Version\HyBi10($component))
            ->enableVersion(new Version\Hixie76)
        ;

        $this->_decorating = $component;
        $this->connections = new \SplObjectStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
        $conn->WebSocket = new \StdClass;
        $conn->WebSocket->established = false;
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        if (true !== $from->WebSocket->established) {
            try {
                if (null === ($request = $this->reqParser->onMessage($from, $msg))) {
                    return;
                }
            } catch (\OverflowException $oe) {
                return $this->close($from, 413);
            }

            if (!$this->versioner->isVersionEnabled($request)) {
                return $this->close($from);
            }

            $from->WebSocket->request = $request;
            $from->WebSocket->version = $this->versioner->getVersion($request);

            $response = $from->WebSocket->version->handshake($request);
            $response->setHeader('X-Powered-By', \Ratchet\VERSION);

            // This needs to be refactored later on, incorporated with routing
            if ('' !== ($agreedSubProtocols = $this->getSubProtocolString($request->getTokenizedHeader('Sec-WebSocket-Protocol', ',')))) {
                $response->setHeader('Sec-WebSocket-Protocol', $agreedSubProtocols);
            }

            $from->send((string)$response);

            if (101 != $response->getStatusCode()) {
                return $from->close();
            }

            $upgraded = $from->WebSocket->version->upgradeConnection($from, $this->_decorating);

            $this->connections->attach($from, $upgraded);

            $upgraded->WebSocket->established = true;

            return $this->_decorating->onOpen($upgraded);
        }

        $from->WebSocket->version->onMessage($this->connections[$from], $msg);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        if ($this->connections->contains($conn)) {
            $decor = $this->connections[$conn];
            $this->connections->detach($conn);
        }

        // WS::onOpen is not called when the socket connects, it's call when the handshake is done
        // The socket could close before WS calls onOpen, so we need to check if we've "opened" it for the developer yet
        if (isset($decor)) {
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

    protected function close(ConnectionInterface $conn, $code = 400) {
        $response = new Response($code, array(
            'Sec-WebSocket-Version' => $this->versioner->getSupportedVersionString()
          , 'X-Powered-By'          => \Ratchet\VERSION
        ));

        $conn->send((string)$response);
        $conn->close();
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
     * @param Traversable
     * @return string
     */
    protected function getSubProtocolString(\Traversable $requested = null) {
        if (null === $requested) {
            return '';
        }

        $string = '';

        foreach ($requested as $sub) {
            if ($this->isSubProtocolSupported($sub)) {
                $string .= $sub . ',';
            }
        }

        return substr($string, 0, -1);
    }
}