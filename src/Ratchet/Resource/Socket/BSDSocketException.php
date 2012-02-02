<?php
namespace Ratchet\Resource\Socket;

/**
 * Uses internal php methods to fill an Exception class (no parameters required)
 */
class BSDSocketException extends \Exception {
    /**
     * @var BSDSocket
     */
    protected $_socket;

    public function __construct(BSDSocket $socket) {
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