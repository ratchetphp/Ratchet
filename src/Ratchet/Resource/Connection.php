<?php
namespace Ratchet\Resource;
use Ratchet\Resource\Socket\SocketInterface;

/**
 * A proxy object representing a connection to the application
 * This acts as a container to storm data (in memory) about the connection
 */
class Connection implements ConnectionInterface {
    protected $_data = array();

    /**
     * @var Ratchet\Resource\Socket\SocketInterface
     */
    protected $_socket;

    public function __construct(SocketInterface $socket) {
        $this->_socket = $socket;
    }

    /**
     * @return int
     */
    public function getID() {
        return (int)(string)$this->_socket;
    }

    /**
     * This is here because I couldn't figure out a better/easier way to tie a connection and socket together for the server and commands
     * Anyway, if you're here, it's not recommended you use this/directly interact with the socket in your App...
     * The command pattern (which is fully flexible, see Runtime) is the safest, desired way to interact with the socket(s).
     * @return Ratchet\SocketInterface
     * @todo Figure out a better way to match Socket/Connection in Application and Commands
     */
    public function getSocket() {
        return $this->_socket;
    }

    /**
     * @{inheritdoc}
     */
    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }

    /**
     * @{inheritdoc}
     */
    public function __get($name) {
        if (!$this->__isset($name)) {
            throw new \InvalidArgumentException("Attribute '{$name}' not found in Connection {$this->getID()}");
        }

        if (is_callable($this->_data[$name])) {
            return $this->_data[$name]($this);
        } else {
            return $this->_data[$name];
        }
    }

    /**
     * @{inheritdoc}
     */
    public function __isset($name) {
        return isset($this->_data[$name]);
    }

    /**
     * @{inheritdoc}
     */
    public function __unset($name) {
        unset($this->_data[$name]);
    }
}