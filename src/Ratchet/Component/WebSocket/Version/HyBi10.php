<?php
namespace Ratchet\Component\WebSocket\Version;
use Guzzle\Http\Message\RequestInterface;

/**
 * @todo Note: Even though this is the "legacy" HyBi version, it's using the RFC Message and Frame classes - change if needed
 */
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