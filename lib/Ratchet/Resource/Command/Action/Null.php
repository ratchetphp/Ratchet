<?php
namespace Ratchet\Resource\Command\Action;
use Ratchet\Resource\Command\ActionTemplate;
use Ratchet\ObserverInterface;

/**
 * Null pattern - execution does nothing, something needs to be passed back though
 */
class Null extends ActionTemplate {
    public function execute(ObserverInterface $scope = null) {
    }
}