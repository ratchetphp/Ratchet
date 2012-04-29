<?php
namespace Ratchet\Component\Server;
use Ratchet\Component\MessageComponentInterface;
use Ratchet\Resource\ConnectionInterface;
use Ratchet\Resource\Command\Action\CloseConnection;

class IpBlackListComponent implements MessageComponentInterface {
    /**
     * @var array
     */
    protected $_blacklist = array();

    /**
     * @var Ratchet\Component\MessageComponentInterface
     */
    protected $_decorating;

    public function __construct(MessageComponentInterface $component) {
        $this->_decorating = $component;
    }

    /**
     * @param string IP address to block from connecting to yoru application
     * @return IpBlackList
     */
    public function blockAddress($ip) {
        $this->_blacklist[$ip] = true;

        return $this;
    }

    /**
     * @param string IP address to unblock from connecting to yoru application
     * @return IpBlackList
     */
    public function unblockAddress($ip) {
        if (isset($this->_blacklist[$this->filterAddress($ip)])) {
            unset($this->_blacklist[$this->filterAddress($ip)]);
        }

        return $this;
    }

    /**
     * @param string
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
     * @param string
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
            return new CloseConnection($conn);
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
        if ($this->isBlocked($conn->remoteAddress)) {
            return null;
        }

        return $this->_decorating->onClose($conn);
    }

    /**
     * {@inheritdoc}
     */
    function onError(ConnectionInterface $conn, \Exception $e) {
        return $this->_decorating->onError($conn, $e);
    }
}