<?php
namespace Ratchet\Resource;
use Ratchet\SocketInterface;

/**
 * @todo Consider if this belongs under Application
 */
class Connection {
    protected $_data = array();

    /**
     * @var Ratchet\SocketInterface
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
     * Set an attribute to the connection
     * @param mixed
     * @param mixed
     */
    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }

    /**
     * Get a previously set attribute bound to the connection
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __get($name) {
        if (!isset($this->_data[$name])) {
            throw new \InvalidArgumentException("Attribute '{$name}' not found in Connection {$this->getID()}");
        }

        if (is_callable($this->_data[$name])) {
            return $this->_data[$name]($this);
        } else {
            return $this->_data[$name];
        }
    }
}