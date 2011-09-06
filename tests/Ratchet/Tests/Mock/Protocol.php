<?php
namespace Ratchet\Tests\Mock;
use Ratchet\Protocol\ProtocolInterface;

class Protocol implements ProtocolInterface {
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

    public function getName() {
        return 'mock_protocol';
    }

    public function handleConnect() {
    }

    public function handleMessage() {
    }

    public function handleClose() {
    }
}