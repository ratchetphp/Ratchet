<?php
namespace Ratchet\Tests\Mock;
use Ratchet\SocketObserver;
use Ratchet\Server;
use Ratchet\Tests\Mock\Socket as MockSocket;
use Ratchet\SocketInterface;

class Application implements SocketObserver {
    public function onOpen(SocketInterface $conn) {
    }

    public function onRecv(SocketInterface $from, $msg) {
    }

    public function onClose(SocketInterface $conn) {
    }

    public function onError(SocketInterface $conn, \Exception $e) {
    }
}