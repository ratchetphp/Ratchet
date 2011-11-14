<?php
namespace Ratchet\Tests\Mock;
use Ratchet\Application\ApplicationInterface;
use Ratchet\Tests\Mock\Socket as MockSocket;
use Ratchet\Resource\Connection;

class Application implements ApplicationInterface {
    public function __construct(ApplicationInterface $app = null) {
    }

    public function onOpen(Connection $conn) {
    }

    public function onRecv(Connection $from, $msg) {
    }

    public function onClose(Connection $conn) {
    }

    public function onError(Connection $conn, \Exception $e) {
    }
}