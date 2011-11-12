<?php
namespace Ratchet\Command\Action;
use Ratchet\Command\ActionTemplate;
use Ratchet\SocketObserver;

/**
 * Null pattern - execution does nothing, something needs to be passed back though
 */
class Null extends ActionTemplate {
    public function execute(SocketObserver $scope = null) {
    }
}