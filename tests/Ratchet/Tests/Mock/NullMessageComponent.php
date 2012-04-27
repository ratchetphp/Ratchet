<?php
namespace Ratchet\Tests\Mock;
use Ratchet\Component\MessageComponentInterface;
use Ratchet\Resource\ConnectionInterface;

class NullMessageComponent implements MessageComponentInterface {
    /**
     * @var SplObjectStorage
     */
    public $connections;

    /**
     * @var SplQueue
     */
    public $messageHistory;

    /**
     * @var SplQueue
     */
    public $errorHistory;

    public function __construct() {
        $this->connections    = new \SplObjectStorage;
        $this->messageHistory = new \SplQueue;
        $this->errorHistory   = new \SplQueue;
    }

    /**
     * {@inheritdoc}
     */
    function onOpen(ConnectionInterface $conn) {
        $this->connections->attach($conn);
    }

    /**
     * {@inheritdoc}
     */
    function onMessage(ConnectionInterface $from, $msg) {
        $this->messageHistory->enqueue(array('from' => $from, 'msg' => $msg));
    }

    /**
     * {@inheritdoc}
     */
    function onClose(ConnectionInterface $conn) {
        $this->connections->detach($conn);
    }

    /**
     * {@inheritdoc}
     */
    function onError(ConnectionInterface $conn, \Exception $e) {
        $this->errorHistory->enqueue(array('conn' => $conn, 'exception' => $e));
    }
}