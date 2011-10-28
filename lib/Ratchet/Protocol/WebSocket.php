<?php
namespace Ratchet\Protocol;
use Ratchet\Server;
use Ratchet\Protocol\WebSocket\Client;
use Ratchet\Protocol\WebSocket\Version;
use Ratchet\SocketInterface;
use Ratchet\ReceiverInterface;

/**
 * @link http://ca.php.net/manual/en/ref.http.php
 * @todo Make sure this works both ways (client/server) as stack needs to exist on client for framing
 * @todo Clean up Client/Version stuff.  This should be a factory making single instances of Version classes, implement chain of reponsibility for version - client should implement an interface?
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
     * @type Ratchet\ReceiverInterface
     */
    protected $_app;

    protected $_versions = array(
        'HyBi10'  => null
      , 'Hixie76' => null
    );

    public function __construct(ReceiverInterface $application) {
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
        $client = $this->_lookup[$from];
        if (true !== $client->isHandshakeComplete()) {

// remove client, get protocol, do handshake, return, etc

            $headers  = $this->getHeaders($msg);
            $response = $client->setVersion($this->getVersion($headers))->doHandshake($headers);

            $header = '';
            foreach ($response as $key => $val) {
                if (!empty($key)) {
                    $header .= "{$key}: ";
                }

                $header .= "{$val}\r\n";
            }
            $header .= "\r\n";
//            $header   = implode("\r\n", $response) . "\r\n";

//            $from->write($header, strlen($header));
            $to  = new \Ratchet\SocketCollection;
            $to->enqueue($from);
            $cmd = new \Ratchet\Command\SendMessage($to);
            $cmd->setMessage($header);

            // call my decorated onRecv()

$this->_server->log('Returning handshake: ' . $header);

            return $cmd;
        }

        try {
            $msg = $client->getVersion()->unframe($msg);
            if (is_array($msg)) { // temporary
                $msg = $msg['payload'];
            }

        } catch (\UnexpectedValueException $e) {
            $to  = new \Ratchet\SocketCollection;
            $to->enqueue($from);
            $cmd = new \Ratchet\Command\Close($to);

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
     * @todo Consider strtolower all the header keys...right now PHP Changes Sec-WebSocket-X to Sec-Websocket-X...this could change
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
                if (null === $this->_versions['HyBi10']) {
                    $this->_versions['HyBi10'] = new Version\Hybi10;
                }

                return $this->_versions['HyBi10'];
            }
        } elseif (isset($headers['Sec-Websocket-Key2'])) { // Hixie
        }

        throw new \UnexpectedValueException('Could not identify WebSocket protocol');
    }
}