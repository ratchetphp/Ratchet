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

    public function doHandshake(VersionInterface $version) {
        $key = $version->sign();
//        $tosend['Sec-WebSocket-Accept'] = $key;

        $this->_hands_shook = true;

        return "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: {$key}\r\nSec-WebSocket-Protocol: test\r\n\r\n";
    }

    /**
     * @return bool
     */
    public function isHandshakeComplete() {
        return $this->_hands_shook;
    }
}