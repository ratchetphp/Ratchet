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
    }

    function handleConnect(Socket $client) {
    }

    function handleMessage($message, Socket $from) {
    }

    function handleClose(Socket $client) {
    }
}