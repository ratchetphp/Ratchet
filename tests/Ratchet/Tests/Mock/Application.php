<?php
namespace Ratchet\Tests\Mock;
use Ratchet\ObserverInterface;
use Ratchet\Tests\Mock\Socket as MockSocket;
use Ratchet\SocketInterface;

class Application implements ObserverInterface {
    public function __construct(ObserverInterface $app = null) {
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