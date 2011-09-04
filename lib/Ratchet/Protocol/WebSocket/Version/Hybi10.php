<?php
namespace Ratchet\Protocol\WebSocket\Version;

class Hybi10 implements VersionInterface {
    const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    public function sign($key) {
        return base64_encode(sha1($key . static::GUID, 1));
    }
}