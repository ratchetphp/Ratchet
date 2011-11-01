<?php
namespace Ratchet\Command;
use Ratchet\SocketInterface;

/**
 * Null pattern - execution does nothing, something needs to be passed back though
 */
class Null implements CommandInterface {
    public function __construct(SocketInterface $socket) {
    }

    public function execute() {
    }
}