<?php
namespace Ratchet\Tests\Mock;
use Ratchet\ReceiverInterface;
use Ratchet\Server;
use Ratchet\Tests\Mock\Socket as MockSocket;
use Ratchet\SocketInterface;

class Application implements ReceiverInterface {
    public function getName() {
        return 'mock_application';
    }

    public function setUp(Server $server) {
    }

    public function onOpen(SocketInterface $conn) {
    }

    public function onRecv(SocketInterface $from, $msg) {
    }

    public function onClose(SocketInterface $conn) {
    }
}