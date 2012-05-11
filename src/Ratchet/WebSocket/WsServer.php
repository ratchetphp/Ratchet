<?php
namespace Ratchet\WebSocket;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Guzzle\Http\Message\RequestInterface;
use Ratchet\WebSocket\Guzzle\Http\Message\RequestFactory;

/**
 * The adapter to handle WebSocket requests/responses
 * This is a mediator between the Server and your application to handle real-time messaging through a web browser
 * @todo Separate this class into a two classes: Component and a protocol handler
 * @link http://ca.php.net/manual/en/ref.http.php
 * @link http://dev.w3.org/html5/websockets/
 */
class WsServer implements MessageComponentInterface {
    /**
     * Decorated component
     * @var Ratchet\MessageComponentInterface
     */
    protected $_decorating;

    /**
     * @var SplObjectStorage
     */
    protected $connections;

    /**
     * Re-entrant instances of protocol version classes
     * @internal
     */
    protected $_versions = array(
        'HyBi10'  => null
      , 'Hixie76' => null
      , 'RFC6455' => null
    );

    protected $_mask_payload = false;

    /**
     * For now, array_push accepted subprotocols to this array
     * @deprecated
     * @temporary
     */
    public $accepted_subprotocols = array();

    public function __construct(MessageComponentInterface $component) {
        $this->_decorating = $component;
        $this->connections = new \SplObjectStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
        $conn->WebSocket = new \stdClass;
        $conn->WebSocket->handshake = false;
        $conn->WebSocket->headers   = '';
    }

    /**
     * Do handshake, frame/unframe messages coming/going in stack
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        if (true !== $from->WebSocket->handshake) {
            if (!isset($from->WebSocket->version)) {
                $from->WebSocket->headers .= $msg;
                if (!$this->isMessageComplete($from->WebSocket->headers)) {
                    return;
                }

                $headers = RequestFactory::getInstance()->fromMessage($from->WebSocket->headers);
                $from->WebSocket->version = $this->getVersion($headers);
                $from->WebSocket->headers = $headers;
            }

            $response = $from->WebSocket->version->handshake($from->WebSocket->headers);
            $from->WebSocket->handshake = true;

            // This block is to be moved/changed later
            $agreed_protocols    = array();
            $requested_protocols = $from->WebSocket->headers->getTokenizedHeader('Sec-WebSocket-Protocol', ',');
            if (null !== $requested_protocols) {
                foreach ($this->accepted_subprotocols as $sub_protocol) {
                    if (false !== $requested_protocols->hasValue($sub_protocol)) {
                        $agreed_protocols[] = $sub_protocol;
                    }
                }
            }
            if (count($agreed_protocols) > 0) {
                $response->setHeader('Sec-WebSocket-Protocol', implode(',', $agreed_protocols));
            }
            $response->setHeader('X-Powered-By', \Ratchet\VERSION);
            $header = (string)$response;

            $from->send($header);

            $conn = new WsConnection($from);
            $this->connections->attach($from, $conn);

            return $this->_decorating->onOpen($conn);
        }

        if (!isset($from->WebSocket->message)) {
            $from->WebSocket->message = $from->WebSocket->version->newMessage();
        }

        // There is a frame fragment attatched to the connection, add to it
        if (!isset($from->WebSocket->frame)) {
            $from->WebSocket->frame = $from->WebSocket->version->newFrame();
        }

        $from->WebSocket->frame->addBuffer($msg);
        if ($from->WebSocket->frame->isCoalesced()) {
            if ($from->WebSocket->frame->getOpcode() > 2) {
                $from->end();
                throw new \UnexpectedValueException('Control frame support coming soon!');
            }
            // Check frame
            // If is control frame, do your thing
            // Else, add to message
            // Control frames (ping, pong, close) can be sent in between a fragmented message

            $from->WebSocket->message->addFrame($from->WebSocket->frame);
            unset($from->WebSocket->frame);
        }

        if ($from->WebSocket->message->isCoalesced()) {
            $this->_decorating->onMessage($this->connections[$from], (string)$from->WebSocket->message);
            unset($from->WebSocket->message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        // WS::onOpen is not called when the socket connects, it's call when the handshake is done
        // The socket could close before WS calls onOpen, so we need to check if we've "opened" it for the developer yet
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
        if ($this->connections->contains($conn)) {
            $this->_decorating->onError($this->connections[$conn], $e);
        } else {
            $conn->close();
        }
    }

    /**
     * Detect the WebSocket protocol version a client is using based on the HTTP header request
     * @param string HTTP handshake request
     * @return Version\VersionInterface
     * @throws UnderFlowException If we think the entire header message hasn't been buffered yet
     * @throws InvalidArgumentException If we can't understand protocol version request
     * @todo Verify the first line of the HTTP header as per page 16 of RFC 6455
     */
    protected function getVersion(RequestInterface $request) {
        foreach ($this->_versions as $name => $instance) {
            if (null !== $instance) {
                if ($instance::isProtocol($request)) {
                    return $instance;
                }
            } else {
                $ns = __NAMESPACE__ . "\\Version\\{$name}";
                if ($ns::isProtocol($request)) {
                    $this->_versions[$name] = new $ns;
                    return $this->_versions[$name];
                }
            }
        }

        throw new \InvalidArgumentException('Could not identify WebSocket protocol');
    }

    /**
     * @param string
     * @return bool
     * @todo Abstract, some hard coding done for (stupid) Hixie protocol
     */
    protected function isMessageComplete($message) {
        static $crlf = "\r\n\r\n";

        $headers = (boolean)strstr($message, $crlf);
        if (!$headers) {

            return false;
        }

        if (strstr($message, 'Sec-WebSocket-Key2')) {
            if (8 !== strlen(substr($message, strpos($message, $crlf) + strlen($crlf)))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Disable a version of the WebSocket protocol *cough*Hixie76*cough*
     * @param string The name of the version to disable
     * @throws InvalidArgumentException If the given version does not exist
     */
    public function disableVersion($name) {
        if (!array_key_exists($name, $this->_versions)) {
            throw new \InvalidArgumentException("Version {$name} not found");
        }

        unset($this->_versions[$name]);
    }

    /**
     * Set the option to mask the payload upon sending to client
     * If WebSocket is used as server, this should be false, client to true
     * @param bool
     * @todo User shouldn't have to know/set this, need to figure out how to do this automatically
     */
    public function setMaskPayload($opt) {
        $this->_mask_payload = (boolean)$opt;
    }
}