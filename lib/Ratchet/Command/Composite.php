<?php
namespace Ratchet\Command;
use Ratchet\SocketObserver;

class Composite extends \SplQueue implements CommandInterface {
    public function enqueue(CommandInterface $command) {
        if ($command instanceof Composite) {
            foreach ($command as $cmd) {
                $this->enqueue($cmd);
            }

            return;
        }

        parent::enqueue($command);
    }

    public function execute(SocketObserver $scope = null) {
        $this->setIteratorMode(static::IT_MODE_DELETE);

        $recursive = new self;

        foreach ($this as $command) {
            $ret = $command->execute($scope);

            if ($ret instanceof CommandInterface) {
                $recursive->enqueue($ret);
            }
        }

        if (count($recursive) > 0) {
            return $recursive;
        }
    }
}