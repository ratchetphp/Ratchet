<?php

namespace Ratchet\Server;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class IpBlackList implements MessageComponentInterface {
    /**
     * @var array
     */
    protected $_blacklist = [];

    /**
     * @var \Ratchet\MessageComponentInterface
     */
    protected $_decorating;

    /**
     * @param  string $address
     * @return bool
     */
    public function isBlocked($address) {
        return (isset($this->_blacklist[$this->filterAddress($address)]));
    }

    /**
     * @param  string $address
     * @return string
     */
    public function filterAddress($address) {
        if (strstr($address, ':') && substr_count($address, '.') == 3) {
            [$address, $port] = explode(':', $address);
        }

        return $address;
    }

    #[\Override]
    function onOpen(ConnectionInterface $conn) {
        if ($this->isBlocked($conn->remoteAddress)) {
            return $conn->close();
        }

        return $this->_decorating->onOpen($conn);
    }

    #[\Override]
    function onMessage(ConnectionInterface $from, $msg) {
        return $this->_decorating->onMessage($from, $msg);
    }

    #[\Override]
    function onClose(ConnectionInterface $conn) {
        if (! $this->isBlocked($conn->remoteAddress)) {
            $this->_decorating->onClose($conn);
        }
    }

    #[\Override]
    function onError(ConnectionInterface $conn, \Exception $e) {
        if (! $this->isBlocked($conn->remoteAddress)) {
            $this->_decorating->onError($conn, $e);
        }
    }
}
