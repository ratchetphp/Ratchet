<?php
namespace Ratchet\Application\WebSocket;
use Ratchet\Application\WebSocket\Client;
use Ratchet\Application\WebSocket\VersionInterface;
use Ratchet\SocketInterface;
use Ratchet\Application\ApplicationInterface;
use Ratchet\Application\ConfiguratorInterface;
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
 * @todo Consider cheating the application...don't call _app::onOpen until handshake is complete - only issue is sending headers/cookies
 * @todo Consider chaning this class to a State Pattern.  If a ObserverInterface is passed in __construct, do what is there now.  If it's an AppInterface change behaviour of socket interaction (onOpen, handshake, etc)
 * @todo Change namespace to Ratchet\Application\WebSocket\Adapter
 */
class App implements ApplicationInterface, ConfiguratorInterface {
    /**
     * Lookup for connected clients
     * @var SplObjectStorage
     */
    protected $_clients;

    /**
     * Decorated application
     * @var Ratchet\ObserverInterface
     */
    protected $_app;

    /**
     * @var Ratchet\Resource\Command\Factory
     */
    protected $_factory;

    /**
     * @internal
     */
    protected $_versions = array(
        'HyBi10'  => null
      , 'Hixie76' => null
    );

    public function __construct(ApplicationInterface $app = null) {
        if (null === $app) {
            throw new \UnexpectedValueException("WebSocket requires an application to run off of");
        }

        $this->_clients = new \SplObjectStorage;
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

    public function onOpen(SocketInterface $conn) {
        $this->_clients[$conn] = new Client;
    }

    public function onRecv(SocketInterface $from, $msg) {
        $client = $this->_clients[$from];
        if (true !== $client->isHandshakeComplete()) {
            $response = $client->setVersion($this->getVersion($msg))->doHandshake($msg);

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

        $msg = $client->getVersion()->unframe($msg);
        if (is_array($msg)) { // temporary
            $msg = $msg['payload'];
        }

        $cmds = $this->_app->onRecv($from, $msg);
        return $this->prepareCommand($cmds);
    }

    public function onClose(SocketInterface $conn) {
        $cmds = $this->prepareCommand($this->_app->onClose($conn));

        // $cmds = new Composite if null
        // $cmds->enqueue($this->_factory->newCommand('SendMessage', $conn)->setMessage(
            // WebSocket close handshake, depending on version!
        //));

        unset($this->_clients[$conn]);
        return $cmds;
    }

    public function onError(SocketInterface $conn, \Exception $e) {
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
            $version = $this->_clients[$command->getSocket()]->getVersion();
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