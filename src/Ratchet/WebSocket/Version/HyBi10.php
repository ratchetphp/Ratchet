<?php
namespace Ratchet\WebSocket\Version;
use Guzzle\Http\Message\RequestInterface;

class HyBi10 extends RFC6455 {
    public function isProtocol(RequestInterface $request) {
        $version = (int)(string)$request->getHeader('Sec-WebSocket-Version');

        return ($version >= 6 && $version < 13);
    }

    public function getVersionNumber() {
        return 6;
    }
}