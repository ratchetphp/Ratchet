<?php
namespace Ratchet\Application\WebSocket\Version;

class HyBi10 extends RFC6455 {
    public static function isProtocol(array $headers) {
        if (isset($headers['Sec-Websocket-Version'])) {
            if ((int)$headers['Sec-Websocket-Version'] >= 6 && (int)$headers['Sec-Websocket-Version'] < 13) {
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