<?php
namespace Ratchet\Application\WebSocket\Version;

/**
 * @todo Note: Even though this is the "legacy" HyBi version, it's using the RFC Message and Frame classes - change if needed
 */
class HyBi10 extends RFC6455 {
    public static function isProtocol($headers) {
        if (isset($headers['Sec-WebSocket-Version'])) {
            if ((int)$headers['Sec-WebSocket-Version'] >= 6 && (int)$headers['Sec-WebSocket-Version'] < 13) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return HyBi10\Message
     * /
    public function newMessage() {
        return new HyBi10\Message;
    }

    /**
     * @return HyBi10\Frame
     * /
    public function newFrame() {
        return new HyBi10\Frame;
    }
    /**/
}