<?php
namespace Ratchet\Resource\Command;
use Ratchet\Component\ComponentInterface;

class Composite extends \SplQueue implements CommandInterface {
    /**
     * Add another Command to the stack
     * Unlike a true composite the enqueue flattens a composite parameter into leafs
     * @param CommandInterface|null
     */
    public function enqueue($command) {
        if (null === $command) {
            return;
        }

        if (!($command instanceof CommandInterface)) {
            throw new \InvalidArgumentException("Parameter MUST implement Ratchet.Component.CommandInterface");
        }

        if ($command instanceof self) {
            foreach ($command as $cmd) {
                $this->enqueue($cmd);
            }

            return;
        }

        parent::enqueue($command);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ComponentInterface $scope = null) {
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