<?php
namespace Ratchet\Command;
use Ratchet\SocketInterface;

class Composite extends \SplQueue {
    public function enqueue(CommandInterface $command) {
        return parent::enqueue($command);
    }

    public function execute() {
        $this->setIteratorMode(static::IT_MODE_DELETE);

        foreach ($this as $command) {
            $command->execute();
        }
    }
}