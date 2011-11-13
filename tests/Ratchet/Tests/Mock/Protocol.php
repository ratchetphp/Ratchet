<?php
namespace Ratchet\Tests\Mock;
use Ratchet\ObserverInterface;
use Ratchet\Application\ProtocolInterface;
use Ratchet\Server;
use Ratchet\SocketInterface;

class Protocol implements ProtocolInterface {
    public function __construct(ObserverInterface $app = null) {
    }

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
    }

    public function onRecv(SocketInterface $from, $msg) {
    }

    public function onClose(SocketInterface $conn) {
    }

    public function onError(SocketInterface $conn, \Exception $e) {
    }
}