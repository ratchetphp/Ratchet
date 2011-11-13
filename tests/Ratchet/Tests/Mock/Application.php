<?php
namespace Ratchet\Tests\Mock;
use Ratchet\Application\ApplicationInterface;
use Ratchet\Tests\Mock\Socket as MockSocket;
use Ratchet\SocketInterface;

class Application implements ApplicationInterface {
    public function __construct(ApplicationInterface $app = null) {
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