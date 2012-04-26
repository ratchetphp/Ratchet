<?php
namespace Ratchet\Resource\Command\Action;
use Ratchet\Component\ComponentInterface;

/**
 * Send text back to the client end of the socket(s)
 */
class SendMessage extends ActionTemplate {
    /**
     * @var string
     */
    protected $_message = '';

    /**
     * The message to send to the socket(s)
     * @param string
     * @return SendMessage Fluid interface
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
     * {@inheritdoc}
     * @throws \UnexpectedValueException if a message was not set with setMessage()
     */
    public function execute(ComponentInterface $scope = null) {
        if (empty($this->_message)) {
            throw new \UnexpectedValueException("Message is empty");
        }

        $this->getConnection()->getSocket()->deliver($this->_message);
    }
}