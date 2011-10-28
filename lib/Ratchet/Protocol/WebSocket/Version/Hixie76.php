<?php
namespace Ratchet\Protocol\WebSocket\Version;

class Hixie76 implements VersionInterface {
    public function handshake(array $headers) {
    }

    public function unframe($message) {
    }

    public function frame($message) {
    }

    public function sign($key) {
    }

    /**
     * What was I doing here?
     * @param Headers
     * @return string
     */
    public function concatinateKeyString($headers) {
    }
}