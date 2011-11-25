<?php
namespace Ratchet\Application\WebSocket;
use Ratchet\Application\ApplicationInterface;
use Ratchet\Application\ConfiguratorInterface;
use Ratchet\Resource\Connection;
use Ratchet\Resource\Command\Factory;
use Ratchet\Resource\Command\CommandInterface;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Application\WebSocket\Util\HTTP;
use Ratchet\Application\WebSocket\Version;

/**
 * The adapter to handle WebSocket requests/responses
 * This is a mediator between the Server and your application to handle real-time messaging through a web browser
 * @link http://ca.php.net/manual/en/ref.http.php
 * @todo Make sure this works both ways (client/server) as stack needs to exist on client for framing
 * @todo Learn about closing the socket.  A message has to be sent prior to closing - does the message get sent onClose event or CloseConnection command?
 * @todo Consider chaning this class to a State Pattern.  If a WS App interface is passed use different state for additional methods used
 */
class App implements ApplicationInterface, ConfiguratorInterface {
    /**
     * Decorated application
     * @var Ratchet\Application\ApplicationInterface
     */
    protected $_app;

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
    );

    protected $_mask_payload = false;

    public function __construct(ApplicationInterface $app = null) {
        if (null === $app) {
            throw new \UnexpectedValueException("WebSocket requires an application to run");
        }

        $this->_app     = $app;
        $this->_factory = new Factory;
    }

    /**
     * Return the desired socket configuration if hosting a WebSocket server
     * This method may be removed
     * @return array
     */
    public static function getDefaultConfig() {
        return array(
            'domain'   => AF_INET
          , 'type'     => SOCK_STREAM
          , 'protocol' => SOL_TCP
          , 'options'  => array(
                SOL_SOCKET => array(SO_REUSEADDR => 1)
            )
        );
    }

    public function onOpen(Connection $conn) {
        $conn->WebSocket = new \stdClass;
        $conn->WebSocket->handshake = false;
        $conn->WebSocket->headers   = '';
    }

    /**
     * Do handshake, frame/unframe messages coming/going in stack
     * @todo This needs some major refactoring
     */
    public function onMessage(Connection $from, $msg) {
        if (true !== $from->WebSocket->handshake) {
            if (!isset($from->WebSocket->version)) {
                try {
                    $from->WebSocket->headers .= $msg;
                    $from->WebSocket->version  = $this->getVersion($from->WebSocket->headers);
                } catch (\UnderflowException $e) {
                    return;
                }
            }

            $response = $from->WebSocket->version->handshake($from->WebSocket->headers);
            $from->WebSocket->handshake = true;

            if (is_array($response)) {
                $header = '';
                foreach ($response as $key => $val) {
                    if (!empty($key)) {
                        $header .= "{$key}: ";
                    }

                    $header .= "{$val}\r\n";
                }
                $header .= "\r\n";
            } else {
                $header = $response;
            }

            $comp = $this->_factory->newComposite();
            $comp->enqueue($this->_factory->newCommand('SendMessage', $from)->setMessage($header));
            $comp->enqueue($this->prepareCommand($this->_app->onOpen($from, $msg))); // Need to send headers/handshake to application, let it have the cookies, etc

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
            $cmds = $this->prepareCommand($this->_app->onMessage($from, (string)$from->WebSocket->message));
            unset($from->WebSocket->message);

            return $cmds;
        }
    }

    public function onClose(Connection $conn) {
        return $this->prepareCommand($this->_app->onClose($conn));
    }

    /**
     * @todo Shouldn't I be using prepareCommand() on the return? look into this
     */
    public function onError(Connection $conn, \Exception $e) {
        return $this->_app->onError($conn, $e);
    }

    /**
     * Incomplete, WebSocket protocol allows client to ask to use a sub-protocol, I'm thinking/wanting to somehow implement this in an application decorated class
     * @param string
     * @todo Implement or delete...
     */
    public function setSubProtocol($name) {
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
     */
    protected function getVersion($message) {
        if (false === strstr($message, "\r\n\r\n")) { // This CAN fail with Hixie, depending on the TCP buffer in between
            throw new \UnderflowException;
        }

        $headers = HTTP::getHeaders($message);

        foreach ($this->_versions as $name => $instance) {
            if (null !== $instance) {
                if ($instance::isProtocol($headers)) {
                    return $instance;
                }
            } else {
                $ns = __NAMESPACE__ . "\\Version\\{$name}";
                if ($ns::isProtocol($headers)) {
                    $this->_versions[$name] = new $ns;
                    return $this->_versions[$name];
                }
            }
        }

        throw new \InvalidArgumentException('Could not identify WebSocket protocol');
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