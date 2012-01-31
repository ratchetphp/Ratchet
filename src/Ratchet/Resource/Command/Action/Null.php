<?php
namespace Ratchet\Resource\Command\Action;
use Ratchet\Component\ComponentInterface;

/**
 * Null pattern - execution does nothing, used when something needs to be passed back
 */
class Null extends ActionTemplate {
    public function execute(ComponentInterface $scope = null) {
    }
}