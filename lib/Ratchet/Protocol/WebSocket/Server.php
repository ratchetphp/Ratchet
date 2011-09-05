<?php
namespace Ratchet\Protocol\WebSocket;
use Ratchet\ServerInterface;
use Ratchet\Protocol\ProtocolInterface;
use Ratchet\ApplicationInterface;

class Server implements ProtocolInterface {
    protected $_server = null;

    public function __construct(ServerInterface $server) {
        $this->_server = $server;
    }

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

    public function attatchApplication(ApplicationInterface $app) {}

    public function run() {
    }
}