<?php
namespace Ratchet\Resource\Command;
use Ratchet\Resource\ConnectionInterface;

abstract class ActionTemplate implements ActionInterface {
    /**
     * @var Ratchet\Resource\Connection
     */
    protected $_conn;

    public function __construct(ConnectionInterface $conn) {
        $this->_conn = $conn;
    }

    public function getConnection() {
        return $this->_conn;
    }
}