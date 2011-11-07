<?php
namespace Ratchet\Protocol;
use Ratchet\Protocol\WebSocket\Client;
use Ratchet\Protocol\WebSocket\VersionInterface;
use Ratchet\SocketInterface;
use Ratchet\SocketObserver;
use Ratchet\Command\CommandInterface;
use Ratchet\Command\Action\SendMessage;
use Ratchet\Command\Composite;
use Ratchet\Protocol\WebSocket\Util\HTTP;

/**
 * The adapter to handle WebSocket requests/responses
 * This is a mediator between the Server and your application to handle real-time messaging through a web browser
 * @link http://ca.php.net/manual/en/ref.http.php
 * @todo Make sure this works both ways (client/server) as stack needs to exist on client for framing
 * @todo Make sure all SendMessage Commands are framed, not just ones received from onRecv
 */
class WebSocket implements ProtocolInterface {
    /**
     * Lookup for connected clients
     * @type SplObjectStorage
     */
    protected $_clients;

    /**
     * Decorated application
     * @type Ratchet\SocketObserver
     */
    protected $_app;

    /**
     * @internal
     */
    protected $_versions = array(
        'HyBi10'  => null
      , 'Hixie76' => null
    );

    public function __construct(SocketObserver $application) {
        $this->_clients = new \SplObjectStorage;
        $this->_app     = $application;
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
        return $this->_app->onOpen($conn);
    }

    /**
     * @todo Cleanup spaghetti code
     */
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

            $cmds = new Composite;
            $mess = new SendMessage($from);
            $mess->setMessage($header);
            $cmds->enqueue($mess);

            return $cmds;
        }

        try {
            $msg = $client->getVersion()->unframe($msg);
            if (is_array($msg)) { // temporary
                $msg = $msg['payload'];
            }
        } catch (\UnexpectedValueException $e) {
            $cmd = new Composite;
            $close = new \Ratchet\Command\Action\CloseConnection($from); // This is to change to Disconnect (proper protocol close)
            $cmd->enqueue($close);

            return $cmd;
        }

        $cmds = $this->_app->onRecv($from, $msg);
        if ($cmds instanceof Composite) {
            foreach ($cmds as $cmd) {
                if ($cmd instanceof SendMessage) {
                    $sock = $cmd->_socket; // bad
                    $clnt = $this->_clients[$sock];

                    $cmd->setMessage($clnt->getVersion()->frame($cmd->getMessage()));
                }
            }
        }
 
        return $cmds;
    }

    /**
     * @todo Wrap any SendMessage commands
     */
    public function onClose(SocketInterface $conn) {
        $cmd = $this->_app->onClose($conn);
        unset($this->_clients[$conn]);
        return $cmd;
    }

    /**
     * @param string
     */
    public function setSubProtocol($name) {
    }

    /**
     * @param \Ratchet\Command\CommandInterface
     * @param Version\VersionInterface
     * @return \Ratchet\Command\CommandInterface
     */
    protected function prepareCommand(CommandInterface $cmd, VersionInterface $version) {
        if ($cmd instanceof SendMessage) {
            $cmd->setMessage($version->frame($cmd->getMessage()));
        }

        return $cmd;
    }

    /**
     * @param array of HTTP headers
     * @return Version\VersionInterface
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
     * @return Version\VersionInterface
     */
    protected function versionFactory($version) {
        if (null === $this->_versions[$version]) {
            $ns = __CLASS__ . "\\Version\\{$version}";
            $this->_version[$version] = new $ns;
        }

        return $this->_version[$version];
    }
}