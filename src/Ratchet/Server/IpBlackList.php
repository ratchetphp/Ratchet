<?php
namespace Ratchet\Server;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class IpBlackList implements MessageComponentInterface {
    /**
     * @var array
     */
    protected $_blacklist = array();

    /**
     * @var \Ratchet\MessageComponentInterface
     */
    protected $_decorating;

    /**
     * @param \Ratchet\MessageComponentInterface $component
     */
    public function __construct(MessageComponentInterface $component) {
        $this->_decorating = $component;
    }

    /**
     * Add an address to the blacklist that will not be allowed to connect to your application
     * @param  string $ip IP address to block from connecting to your application
     * @return IpBlackList
     */
    public function blockAddress($ip) {
        $this->_blacklist[$ip] = true;

        return $this;
    }

    /**
     * Unblock an address so they can access your application again
     * @param string $ip IP address to unblock from connecting to your application
     * @return IpBlackList
     */
    public function unblockAddress($ip) {
        if (isset($this->_blacklist[$this->filterAddress($ip)])) {
            unset($this->_blacklist[$this->filterAddress($ip)]);
        }

        return $this;
    }

    /**
     * @param  string $address
     * @return bool
     */
    public function isBlocked($address) {
        return (isset($this->_blacklist[$this->filterAddress($address)]));
    }

    /**
     * Get an array of all the addresses blocked
     * @return array
     */
    public function getBlockedAddresses() {
        return array_keys($this->_blacklist);
    }

    /**
     * @param  string $address
     * @return string
     */
    public function filterAddress($address) {
        if (strstr($address, ':') && substr_count($address, '.') == 3) {
            list($address, $port) = explode(':', $address);
        }

        return $address;
    }

    /**
     * {@inheritdoc}
     */
    function onOpen(ConnectionInterface $conn) {
        if ($this->isBlocked($conn->remoteAddress)) {
            return $conn->close();
        }

        return $this->_decorating->onOpen($conn);
    }

    /**
     * {@inheritdoc}
     */
    function onMessage(ConnectionInterface $from, $msg) {
        return $this->_decorating->onMessage($from, $msg);
    }

    /**
     * {@inheritdoc}
     */
    function onClose(ConnectionInterface $conn) {
        if (!$this->isBlocked($conn->remoteAddress)) {
            $this->_decorating->onClose($conn);
        }
    }

    /**
     * {@inheritdoc}
     */
    function onError(ConnectionInterface $conn, \Exception $e) {
        if (!$this->isBlocked($conn->remoteAddress)) {
            $this->_decorating->onError($conn, $e);
        }
    }
}
