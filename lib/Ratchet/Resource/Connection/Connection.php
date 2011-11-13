<?php
namespace Ratchet\Resource\Connection;
use Ratchet\SocketInterface;

/**
 * @todo Should I build the commands into this class?  They'd be executed by the Server...
 */
class Connection implements ConnectionInterface {
    /**
     * @var int
     */
    protected $_id;

    protected $_data = array();

    public function __construct(SocketInterface $socket) {
        $this->_id = (string)$socket->getResource();
        $this->_id = (int)substr($this->_id, strrpos($this->_id, '#') + 1);
    }

    /**
     * @return int
     */
    public function getID() {
        return $this->_id;
    }

    public function set($name, $val) {
        $this->_data[$name] = $val;
    }

    public function get($name) {
        if (!isset($this->_data[$name])) {
            throw new \UnexpectedValueException("Attribute '{$name}' not found in Connection {$this->getID()}");
        }

        return $this->_data[$name];
    }
}