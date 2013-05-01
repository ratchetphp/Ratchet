<?php
namespace Ratchet\WebSocket\Version;
use Guzzle\Http\Message\RequestInterface;

class HyBi10 extends RFC6455 {
    public function isProtocol(RequestInterface $request) {
        $version = $request->hasHeader('Sec-WebSocket-Version') ? (int)$request->getHeader('Sec-WebSocket-Version', true) : -1;

        return ($version >= 6 && $version < 13);
    }

    public function getVersionNumber() {
        return 6;
    }
}