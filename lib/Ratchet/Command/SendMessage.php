<?php
namespace Ratchet\Command;
use Ratchet\SocketCollection;

/**
 * Send text back to the client end of the socket(s)
 */
class SendMessage implements CommandInterface {
    /**
     * @var SocketCollection
     */
    protected $_sockets;

    /**
     * @var string
     */
    protected $_message = '';

    public function __construct(SocketCollection $sockets) {
        $this->_sockets = $sockets;
    }

    /**
     * The message to send to the socket(s)
     * @param string
     */
    public function setMessage($msg) {
        $this->_message = (string)$msg;
        return $this;
    }

    /**
     * Get the message from setMessage()
     * @return string
     */
    public function getMessage() {
        return $this->_message;
    }

    /**
     * @throws \UnexpectedValueException if a message was not set with setMessage()
     */
    public function execute() {
        if (empty($this->_message)) {
            throw new \UnexpectedValueException("Message is empty");
        }

        foreach ($this->_sockets as $current) {
            $current->write($this->_message, strlen($this->_message));
        }
    }
}