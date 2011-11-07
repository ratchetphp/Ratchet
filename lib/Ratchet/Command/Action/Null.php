<?php
namespace Ratchet\Command\Action;
use Ratchet\Command\ActionInterface;
use Ratchet\SocketInterface;
use Ratchet\SocketObserver;

/**
 * Null pattern - execution does nothing, something needs to be passed back though
 */
class Null implements ActionInterface {
    public function __construct(SocketInterface $socket) {
    }

    public function execute(SocketObserver $scope = null) {
    }
}