<?php
namespace Ratchet\Protocol\WebSocket;
use Ratchet\Protocol\WebSocket\Version\VersionInterface;

/**
 * A representation of a Socket connection of the user on the other end of the socket
 */
class Client {
    /**
     * @var Ratchet\Protocol\WebSocket\Version\VersionInterface
     */
    protected $_version = null;

    /**
     * @var bool
     */
    protected $_hands_shook = false;

    /**
     * @param Version\VersionInterface
     * @return Client
     */
    public function setVersion(VersionInterface $version) {
        $this->_version = $version;
        return $this;
    }

    /**
     * @return Version\VersionInterface
     */
    public function getVersion() {
        return $this->_version;
    }

    /**
     * @param array
     * @return array
     */
    public function doHandshake(array $headers) {
        $this->_hands_shook = true;

        return $this->_version->handshake($headers);
    }

    /**
     * @return bool
     */
    public function isHandshakeComplete() {
        return $this->_hands_shook;
    }
}