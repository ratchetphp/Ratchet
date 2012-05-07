<?php
namespace Ratchet\Component\WAMP\Resource;
use Ratchet\Resource\AbstractConnectionDecorator;
use Ratchet\Resrouce\ConnectionInterface;

/**
 * @property stdClass $WAMP
 */
class Connection extends AbstractConnectionDecorator {
    public function __construct() {
        // call write() with welcome message
    }

    public function callResponse() {
    }

    public function callError() {
    }

    public function event() {
    }

    public function write($data) {
    }

    public function end() {
    }
}