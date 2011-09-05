<?php
namespace Ratchet\Tests\Mock;
use Ratchet\ReceiverInterface;

class Application implements ReceiverInterface {
    public function getName() {
        return 'mock_application';
    }

    public function handleConnect() {
    }

    public function handleMessage() {
    }

    public function handleClose() {
    }
}