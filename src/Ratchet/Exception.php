<?php
namespace Ratchet;

/**
 * Uses internal php methods to fill an Exception class (no parameters required)
 */
class Exception extends \Exception {
    /**
     * @var SocketInterface
     */
    protected $_socket;

    public function __construct(SocketInterface $socket) {
        $int = socket_last_error();
        $msg = socket_strerror($int);

        $this->_socket = $socket;
        //@socket_clear_error($socket->getResource());

        parent::__construct($msg, $int);
    }

    public function getSocket() {
        return $this->_socket;
    }
}