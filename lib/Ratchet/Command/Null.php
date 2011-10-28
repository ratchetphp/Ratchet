<?php
namespace Ratchet\Command;
use Ratchet\SocketCollection;

/**
 * Null pattern - execution does nothing, something needs to be passed back though
 */
class Null implements CommandInterface {
    public function __construct(SocketCollection $sockets) {
    }

    public function execute() {
    }
}