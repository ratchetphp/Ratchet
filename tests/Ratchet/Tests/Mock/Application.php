<?php
namespace Ratchet\Tests\Mock;
use Ratchet\ReceiverInterface;
use Ratchet\Server;
use Ratchet\Tests\Mock\Socket as MockSocket;
use Ratchet\Socket;

class Application implements ReceiverInterface {
    public function getName() {
        return 'mock_application';
    }

    public function setUp(Server $server) {
    }

    public function handleConnect(Socket $client) {
    }

    public function handleMessage($msg, Socket $from) {
    }

    public function handleClose(Socket $client) {
    }
}