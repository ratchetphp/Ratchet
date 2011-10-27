<?php
namespace Ratchet\Protocol;
use Ratchet\Server;
use Ratchet\Protocol\WebSocket\Client;
use Ratchet\Protocol\WebSocket\Version;
use Ratchet\Server\Message;
use Ratchet\Socket;

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

    public function __construct() {
        $this->_lookup = new \SplObjectStorage;
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
    }

    public function handleConnect(Socket $client) {
        $this->_lookup[$client] = new Client;
    }

    public function handleMessage($message, Socket $from) {
        $headers = $this->getHeaders($message);
        $client  = $this->_lookup[$from];
        if (true !== $client->isHandshakeComplete()) {
            $header = $client->doHandshake($this->getVersion($headers));

            $from->write($header, strlen($header));
        }
    }

    public function handleClose(Socket $client) {
        unset($this->_lookup[$client]);
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