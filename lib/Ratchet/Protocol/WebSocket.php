<?php
namespace Ratchet\Protocol;
use Ratchet\Server;
use Ratchet\Protocol\WebSocket\Client;
use Ratchet\Protocol\WebSocket\Version;
use Ratchet\SocketInterface;
use Ratchet\SocketObserver;

/**
 * @link http://ca.php.net/manual/en/ref.http.php
 */
class WebSocket implements ProtocolInterface {
    /**
     * @type Ratchet\Server
     */
    protected $_server;

    /**
     * @type SplObjectStorage
     */
    protected $_lookup;

    /**
     */
    protected $_app;

    public function __construct(SocketObserver $application) {
        $this->_lookup = new \SplObjectStorage;
        $this->_app    = $application;
    }

    /**
     * @return Array
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

    /**
     * @return string
     */
    public function getName() {
        return 'WebSocket';
    }

    public function setUp(Server $server) {
        $this->_server = $server;
        $this->_app->setUp($server);
    }

    public function onOpen(SocketInterface $conn) {
        $this->_lookup[$conn] = new Client;
        return $this->_app->onOpen($conn);
    }

    public function onRecv(SocketInterface $from, $msg) {
        $client  = $this->_lookup[$from];
        if (true !== $client->isHandshakeComplete()) {
            $headers = $this->getHeaders($msg);
            $header = $client->doHandshake($this->getVersion($headers));

//            $from->write($header, strlen($header));
            $to  = new \Ratchet\SocketCollection;
            $to->enqueue($from);
            $cmd = new \Ratchet\Server\Command\SendMessage($to);
            $cmd->setMessage($header);

            // call my decorated onRecv()

$this->_server->log('Returning handshake: ' . $header);

            return $cmd;
        }

        return $this->_app->onRecv($from, $msg);
    }

    public function onClose(SocketInterface $conn) {
        $cmd = $this->_app->onClose($conn);
        unset($this->_lookup[$conn]);
        return $cmd;
    }

    /**
     * @param string
     */
    public function setSubProtocol($name) {
    }

    /**
     * @param string
     * @return array
     */
    protected function getHeaders($http_message) {
        return http_parse_headers($http_message);
    }

    /**
     * @return Version\VersionInterface
     */
    protected function getVersion(array $headers) {
        if (isset($headers['Sec-Websocket-Version'])) { // HyBi
            if ($headers['Sec-Websocket-Version'] == '8') {
                return new Version\Hybi10($headers);
            }
        } elseif (isset($headers['Sec-Websocket-Key2'])) { // Hixie
        }

        throw new \UnexpectedValueException('Could not identify WebSocket protocol');
    }
}