<?php
namespace Ratchet\Server\Command;
use Ratchet\SocketCollection;

/**
 * @todo Move this command to the WebSocket protocol namespace
 */
class Ping implements CommandInterface {
    public function __construct(SocketCollection $sockets) {
    }

    public function execute() {
    }
}