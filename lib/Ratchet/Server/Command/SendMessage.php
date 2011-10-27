<?php
namespace Ratchet\Server\Command;
use Ratchet\SocketCollection;

class SendMessage implements CommandInterface {
    protected $_sockets;
    protected $_message = '';

    public function __construct(SocketCollection $sockets) {
        $this->_sockets = $sockets;
    }

    public function setMessage($msg) {
        $this->_message = (string)$msg;
    }

    public function getMessage() {
        return $this->_message;
    }

    public function execute() {
        if (empty($this->_message)) {
            throw new \UnexpectedValueException("Message is empty");
        }

        foreach ($this->_sockets as $current) {
            $current->write($this->_message, strlen($this->_message));
        }
    }
}