<?php
namespace Ratchet\Command\Action;
use Ratchet\Command\ActionInterface;
use Ratchet\SocketInterface;
use Ratchet\SocketObserver;

class Runtime implements ActionInterface {
    /**
     * @var SocketInterface
     */
    protected $_socket;

    /**
     * @var Closure
     */
    protected $_command = null;

    public function __construct(SocketInterface $socket) {
        $this->_socket = $socket;
    }

    /**
     * Your closure should accept a single \Ratchet\Socket parameter
     * @param Closure Your closure/lambda to execute when the time comes
     */
    public function setCommand(\Closure $callback) {
        $this->_command = $callback;
    }

    public function execute(SocketObserver $scope = null) {
        return call_user_func($this->_command, $socket);
    }
}