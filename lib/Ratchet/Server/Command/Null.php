<?php
namespace Ratchet\Server\Command;
use Ratchet\SocketCollection;

class Null implements CommandInterface {
    public function __construct(SocketCollection $sockets) {
    }

    public function execute() {
    }
}