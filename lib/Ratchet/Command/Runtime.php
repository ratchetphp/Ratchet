<?php
namespace Ratchet\Command;
use Ratchet\SocketCollection;

class Runtime implements CommandInterface {
    /**
     * @var SocketCollection
     */
    protected $_sockets;

    /**
     * @var Closure
     */
    protected $_command = null;

    public function __construct(SocketCollection $sockets) {
        $this->_socket = $sockets;
    }

    /**
     * Your closure should accept a single \Ratchet\Socket parameter
     * @param Closure Your closure/lambda to execute when the time comes
     */
    public function setCommand(\Closure $callback) {
        $this->_command = $callback;
    }

    public function execute() {
        foreach ($this->_sockets as $socket) {
            return call_user_func($this->_command, $socket);
        }
    }
}