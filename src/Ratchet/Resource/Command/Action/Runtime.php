<?php
namespace Ratchet\Resource\Command\Action;
use Ratchet\Resource\Command\ActionTemplate;
use Ratchet\Component\ComponentInterface;

class Runtime extends ActionTemplate {
    /**
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

    public function execute(ComponentInterface $scope = null) {
        return call_user_func($this->_command, $this->getConnection(), $scope);
    }
}