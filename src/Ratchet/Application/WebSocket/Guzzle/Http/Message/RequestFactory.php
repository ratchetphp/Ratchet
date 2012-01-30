<?php
namespace Ratchet\Application\WebSocket\Guzzle\Http\Message;
use Guzzle\Http\Message\RequestFactory as gReqFac;
use Guzzle\Http\Url;

/**
 * Just slighly changing the Guzzle fromMessage() method to always return an EntityEnclosingRequest instance instead of Request
 */
class RequestFactory extends gReqFac {
    /**
     * @param string
     * @return Guzzle\Http\Message\RequestInterface
     */
    public static function fromRequest($message) {
        $parsed = static::parseMessage($message);

        if (!$parsed) {
            return false;
        }

        return self::fromRequestParts(
            $parsed['method'],
            $parsed['parts'],
            $parsed['headers'],
            $parsed['body'],
            $parsed['protocol'],
            $parsed['protocol_version']
        );
    }

    protected static function fromRequestParts($method, array $parts, $headers = null, $body = null, $protocol = 'HTTP', $protocolVersion = '1.1') {
        return self::requestCreate($method, Url::buildUrl($parts, true), $headers, $body)
                   ->setProtocolVersion($protocolVersion);
    }

    protected static function requestCreate($method, $url, $headers = null, $body = null) {
        $c = static::$entityEnclosingRequestClass;
        $request = new $c($method, $url, $headers);
        $request->setBody($body);

        return $request;
    }
}