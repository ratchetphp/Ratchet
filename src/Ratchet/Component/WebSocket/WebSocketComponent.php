<?php
namespace Ratchet\Component\WebSocket;
use Ratchet\Component\MessageComponentInterface;
use Ratchet\Resource\ConnectionInterface;
use Ratchet\Resource\Command\Factory;
use Ratchet\Resource\Command\CommandInterface;
use Ratchet\Resource\Command\Action\SendMessage;
use Guzzle\Http\Message\RequestInterface;
use Ratchet\Component\WebSocket\Guzzle\Http\Message\RequestFactory;

/**
 * The adapter to handle WebSocket requests/responses
 * This is a mediator between the Server and your application to handle real-time messaging through a web browser
 * @link http://ca.php.net/manual/en/ref.http.php
 * @link http://dev.w3.org/html5/websockets/
 */
class WebSocketComponent implements MessageComponentInterface {
    /**
     * Decorated component
     * @var Ratchet\Component\MessageComponentInterface
     */
    protected $_decorating;

    /**
     * Creates commands/composites instead of calling several classes manually
     * @var Ratchet\Resource\Command\Factory
     */
    protected $_factory;

    /**
     * Singleton* instances of protocol version classes
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
        $this->_factory    = new Factory;
    }

    /**
     * @{inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
        $conn->WebSocket = new \stdClass;
        $conn->WebSocket->handshake = false;
        $conn->WebSocket->headers   = '';
    }

    /**
     * Do handshake, frame/unframe messages coming/going in stack
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        if (true !== $from->WebSocket->handshake) {
            if (!isset($from->WebSocket->version)) {
                $from->WebSocket->headers .= $msg;
                if (!$this->isMessageComplete($from->WebSocket->headers)) {
                    return;
                }

                $headers = RequestFactory::fromRequest($from->WebSocket->headers);
                $from->WebSocket->version = $this->getVersion($headers);
                $from->WebSocket->headers = $headers;
            }

            $response = $from->WebSocket->version->handshake($from->WebSocket->headers);
            $from->WebSocket->handshake = true;

            // This block is to be moved/changed later
            $agreed_protocols    = array();
            $requested_protocols = $from->WebSocket->headers->getTokenizedHeader('Sec-WebSocket-Protocol', ',');
        
            foreach ($this->accepted_subprotocols as $sub_protocol) {
                if (null !== $requested_protocols && false !== $requested_protocols->hasValue($sub_protocol)) {
                    $agreed_protocols[] = $sub_protocol;
                }
            }

            if (count($agreed_protocols) > 0) {
                $response->setHeader('Sec-WebSocket-Protocol', implode(',', $agreed_protocols));
            }
            $header = (string)$response;

            $comp = $this->_factory->newComposite();
            $comp->enqueue($this->_factory->newCommand('SendMessage', $from)->setMessage($header));
            $comp->enqueue($this->prepareCommand($this->_decorating->onOpen($from, $msg))); // Need to send headers/handshake to application, let it have the cookies, etc

            return $comp;
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
            $cmds = $this->prepareCommand($this->_decorating->onMessage($from, (string)$from->WebSocket->message));
            unset($from->WebSocket->message);

            return $cmds;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        return $this->prepareCommand($this->_decorating->onClose($conn));
    }

    /**
     * @todo Shouldn't I be using prepareCommand() on the return? look into this
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        return $this->_decorating->onError($conn, $e);
    }

    /**
     * Checks if a return Command from your application is a message, if so encode it/them
     * @param Ratchet\Resource\Command\CommandInterface|NULL
     * @return Ratchet\Resource\Command\CommandInterface|NULL
     */
    protected function prepareCommand(CommandInterface $command = null) {
        $cache = array();
        return $this->mungCommand($command, $cache);
    }

    /**
     * Does the actual work of prepareCommand
     * Separated to pass the cache array by reference, so we're not framing the same stirng over and over
     * @param Ratchet\Resource\Command\CommandInterface|NULL
     * @param array
     * @return Ratchet\Resource\Command\CommandInterface|NULL
     */
    protected function mungCommand(CommandInterface $command = null, &$cache) {
        if ($command instanceof SendMessage) {
            if (!isset($command->getConnection()->WebSocket->version)) { // Client could close connection before handshake complete or invalid handshake
                return $command;
            }

            $version = $command->getConnection()->WebSocket->version;
            $hash    = md5($command->getMessage()) . '-' . spl_object_hash($version);

            if (!isset($cache[$hash])) {
                $cache[$hash] = $version->frame($command->getMessage(), $this->_mask_payload);
            }

            return $command->setMessage($cache[$hash]);
        }

        if ($command instanceof \Traversable) {
            foreach ($command as $cmd) {
                $cmd = $this->mungCommand($cmd, $cache);
            }
        }

        return $command;
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