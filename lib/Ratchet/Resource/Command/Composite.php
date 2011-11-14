<?php
namespace Ratchet\Resource\Command;
//use Ratchet\ObserverInterface;
use Ratchet\Application\ApplicationInterface;

class Composite extends \SplQueue implements CommandInterface {
    /**
     * Add another Command to the stack
     * Unlike a true composite the enqueue flattens a composite parameter into leafs
     * @param CommandInterface
     */
    public function enqueue(CommandInterface $command = null) {
        if ($command instanceof self) {
            foreach ($command as $cmd) {
                $this->enqueue($cmd);
            }

            return;
        }

        if (null !== $command) {
            parent::enqueue($command);
        }
    }

    public function execute(ApplicationInterface $scope = null) {
        $this->setIteratorMode(static::IT_MODE_DELETE);

        $recursive = new self;

        foreach ($this as $command) {
            $recursive->enqueue($command->execute($scope));
        }

        if (count($recursive) > 0) {
            return $recursive;
        }
    }
}