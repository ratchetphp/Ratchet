<?php
namespace Ratchet\Command;
use Ratchet\SocketInterface;
use Ratchet\Command\ActionInterface;
use Ratchet\SocketObserver;

class Pong implements ActionInterface {
    public function __construct(SocketInterface $socket) {
    }

    public function execute(SocketObserver $scope = null) {
    }
}