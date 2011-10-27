<?php
namespace Ratchet\Protocol\WebSocket\Version;

class Hybi10 implements VersionInterface {
    const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    protected $_headers = array();

    public function __construct(array $headers) {
        $this->_headers = $headers;
    }

    public function sign($key = null) {
if (null === $key) {
    $key = $this->_headers['Sec-Websocket-Key'];
}

        return base64_encode(sha1($key . static::GUID, 1));
    }
}