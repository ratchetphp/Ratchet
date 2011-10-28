<?php
namespace Ratchet\Protocol\WebSocket;
use Ratchet\Protocol\WebSocket\Version\VersionInterface;

class Client {
    /**
     * @type Ratchet\Protocol\WebSocket\Version\VersionInterface
     */
    protected $_version = null;

    /**
     * @type bool
     */
    protected $_hands_shook = false;

    /**
     * @param VersionInterface
     * @return Client
     */
    public function setVersion(VersionInterface $version) {
        $this->_version = $version;
        return $this;
    }

    /**
     * @return VersionInterface
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