<?php
namespace Ratchet\Resource\Command\Action;
use Ratchet\Component\ComponentInterface;

/**
 * This allows you to create a run-time command by using a closure
 */
class Runtime extends ActionTemplate {
    /**
     * The stored closure command to execude
     * @var Closure
     */
    protected $_command = null;

    /**
     * Your closure should accept two parameters (\Ratchet\Resource\Connection, \Ratchet\Component\ComponentInterface) parameter and return a CommandInterface or NULL
     * @param Closure Your closure/lambda to execute when the time comes
     */
    public function setCommand(\Closure $callback) {
        $this->_command = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ComponentInterface $scope = null) {
        $cmd = $this->_command;

        return $cmd($this->getConnection(), $scope);
    }
}