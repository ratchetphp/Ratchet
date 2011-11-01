<?php
namespace Ratchet\Protocol;
use Ratchet\Server;
use Ratchet\Protocol\WebSocket\Client;
use Ratchet\Protocol\WebSocket\Version;
use Ratchet\Protocol\WebSocket\VersionInterface;
use Ratchet\SocketInterface;
use Ratchet\SocketObserver;
use Ratchet\Command\CommandInterface;
use Ratchet\Command\SendMessage;

/**
 * The adapter to handle WebSocket requests/responses
 * This is a mediator between the Server and your application to handle real-time messaging through a web browser
 * @link http://ca.php.net/manual/en/ref.http.php
 * @todo Make sure this works both ways (client/server) as stack needs to exist on client for framing
 * @todo Clean up Client/Version stuff.  This should be a factory making single instances of Version classes, implement chain of reponsibility for version - client should implement an interface?
 * @todo Make sure all SendMessage Commands are framed, not just ones received from onRecv
 * @todo Logic is flawed with Command/SocketCollection and framing - framing is done based on the protocol version of the received, not individual receivers...
 */
class WebSocket implements ProtocolInterface {
    /**
     * @type SplObjectStorage
     */
    protected $_clients;

    /**
     * @type Ratchet\SocketObserver
     */
    protected $_app;

    protected $_versions = array(
        'HyBi10'  => null
      , 'Hixie76' => null
    );

    public function __construct(SocketObserver $application) {
        $this->_clients = new \SplObjectStorage;
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

    public function onOpen(SocketInterface $conn) {
        $this->_clients[$conn] = new Client;
        return $this->_app->onOpen($conn);
    }

    public function onRecv(SocketInterface $from, $msg) {
        $client = $this->_clients[$from];
        if (true !== $client->isHandshakeComplete()) {

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

            $to  = new \Ratchet\SocketCollection;
            $to->enqueue($from);
            $cmd = new \Ratchet\Command\SendMessage($to);
            $cmd->setMessage($header);

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

        $cmd = $this->_app->onRecv($from, $msg);
        if ($cmd instanceof SendMessage) {
            $cmd->setMessage($client->getVersion()->frame($cmd->getMessage()));
        }
 
        return $cmd;
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
     * @param string
     * @return array
     * @todo Consider strtolower all the header keys...right now PHP Changes Sec-WebSocket-X to Sec-Websocket-X...this could change
     * @todo Put in fallback code if http_parse_headers is not a function
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
                    $this->_versions['HyBi10'] = new Version\HyBi10;
                }

                return $this->_versions['HyBi10'];
            }
        } elseif (isset($headers['Sec-Websocket-Key2'])) { // Hixie
        }

        throw new \UnexpectedValueException('Could not identify WebSocket protocol');
    }
}