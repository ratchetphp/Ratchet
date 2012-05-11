<?php
namespace Ratchet\WebSocket\Version;
use Guzzle\Http\Message\RequestInterface;

class HyBi10 extends RFC6455 {
    public static function isProtocol(RequestInterface $request) {
        $version = (int)$request->getHeader('Sec-WebSocket-Version', -1);
        return ($version >= 6 && $version < 13);
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