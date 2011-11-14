<?php
namespace Ratchet\Application\WebSocket;
use Ratchet\Application\ApplicationInterface;
use Ratchet\Application\ConfiguratorInterface;
use Ratchet\Resource\Connection;
use Ratchet\Resource\Command\Factory;
use Ratchet\Resource\Command\CommandInterface;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Application\WebSocket\Util\HTTP;

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

    public function __construct(ApplicationInterface $app = null) {
        if (null === $app) {
            throw new \UnexpectedValueException("WebSocket requires an application to run");
        }

        $this->_app     = $app;
        $this->_factory = new Factory;
    }

    /**
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
    }

    public function onRecv(Connection $from, $msg) {
        if (true !== $from->WebSocket->handshake) {

            // need buffer checks in here

            $from->WebSocket->version = $this->getVersion($msg);
            $response = $from->WebSocket->version->handshake($msg);
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

        // buffer!

        $msg = $from->WebSocket->version->unframe($msg);
        if (is_array($msg)) { // temporary
            $msg = $msg['payload'];
        }

        return $this->prepareCommand($this->_app->onRecv($from, $msg));
    }

    public function onClose(Connection $conn) {
        return $this->prepareCommand($this->_app->onClose($conn));
    }

    public function onError(Connection $conn, \Exception $e) {
        return $this->_app->onError($conn, $e);
    }

    /**
     * @param string
     */
    public function setSubProtocol($name) {
    }

    /**
     * Checks if a return Command from your application is a message, if so encode it/them
     * @param Ratchet\Resource\Command\CommandInterface|NULL
     * @return Ratchet\Resource\Command\CommandInterface|NULL
     */
    protected function prepareCommand(CommandInterface $command = null) {
        if ($command instanceof SendMessage) {
            $version = $command->getConnection()->WebSocket->version;
            return $command->setMessage($version->frame($command->getMessage()));
        }

        if ($command instanceof \Traversable) {
            foreach ($command as $cmd) {
                $cmd = $this->prepareCommand($cmd);
            }
        }

        return $command;
    }

    /**
     * @param array of HTTP headers
     * @return WebSocket\Version\VersionInterface
     */
    protected function getVersion($message) {
        $headers = HTTP::getHeaders($message);

        if (isset($headers['Sec-Websocket-Version'])) { // HyBi
            if ($headers['Sec-Websocket-Version'] == '8') {
                return $this->versionFactory('HyBi10');
            }
        } elseif (isset($headers['Sec-Websocket-Key2'])) { // Hixie
            return $this->versionFactory('Hixie76');
        }

        throw new \UnexpectedValueException('Could not identify WebSocket protocol');
    }

    /**
     * @return WebSocket\Version\VersionInterface
     */
    protected function versionFactory($version) {
        if (null === $this->_versions[$version]) {
            $ns = __NAMESPACE__ . "\\Version\\{$version}";
            $this->_version[$version] = new $ns;
        }

        return $this->_version[$version];
    }
}