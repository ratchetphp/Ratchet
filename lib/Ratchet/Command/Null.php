<?php
namespace Ratchet\Command;
use Ratchet\SocketCollection;

class Null implements CommandInterface {
    public function __construct(SocketCollection $sockets) {
    }

    public function execute() {
    }
}