<?php
namespace Ratchet\Tests\Mock;
use Ratchet\ApplicationInterface;

class Application implements ApplicationInterface {
    public function getName() {
        return 'mock_application';
    }

    public function onConnect() {
    }

    public function onMessage() {
    }

    public function onClose() {
    }
}