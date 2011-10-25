<?php
namespace Ratchet\Protocol;
use Ratchet\Server;
use Ratchet\Server\Client;
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
     * @type Ratchet\Protocol\WebSocket\Version\VersionInterface
     */
    protected $_version = null;

    protected $_shook_hands = false;

    /**
     * @return Array
     */
    public static function getDefaultConfig() {
        return Array(
            'domain'   => AF_INET
          , 'type'     => SOCK_STREAM
          , 'protocol' => SOL_TCP
          , 'options'  => Array(
                SOL_SOCKET => Array(SO_REUSEADDR => 1)
            )
        );
    }

    /**
     * @return string
     */
    function getName() {
        return 'WebSocket';
    }

    public function setUp(Server $server) {
        $this->_server = $server;
    }

    function handleConnect(Socket $client) {
    }

    function handleMessage($message, Socket $from) {
    }

    function handleClose(Socket $client) {
    }
}