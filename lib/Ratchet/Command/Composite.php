<?php
namespace Ratchet\Command;
use Ratchet\SocketInterface;

class Composite extends \SplQueue {
    /**
     * @param string
     * @param Ratchet\SocketInterface
     * @return CommandInterface
     */
    public function NOPEcreate($name, SocketInterface $socket) {
        $class = __NAMESPACE__ . "\\{$name}\\";
        if (!class_exists($class)) {
            throw new \UnexpectedValueException("Command {$name} not found");
        }

        $cmd = new $class($socket);

        if ($cmd instanceof CommandInterface) {
            throw new RuntimeException("{$name} is not a valid command");
        }

        $this->enqueue($cmd);

        return $cmd;
    }

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