<?php
namespace Ratchet\Command;
use Ratchet\SocketInterface;

abstract class ActionTemplate implements ActionInterface {
    /**
     * @var Ratchet\SocketInterface
     */
    protected $_socket;

    public function __construct(SocketInterface $socket) {
        $this->_socket = $socket;
    }

    public function getSocket() {
        return $this->_socket;
    }
}