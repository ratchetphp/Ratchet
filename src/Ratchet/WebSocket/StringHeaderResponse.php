<?php
namespace Ratchet\WebSocket;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\MessageInterface;

/**
 * [internal] A PSR-7 response that casts non-string header values to string before storing them.
 *
 * guzzlehttp/psr7 2.11+ raises `E_USER_DEPRECATED` when a non-string scalar is passed to
 * `withHeader()`/`withAddedHeader()`, and guzzlehttp/psr7 3.0 will reject it outright.
 * `Ratchet\RFC6455\Handshake\ServerNegotiator::handshake()` sets `Sec-WebSocket-Version` from
 * `getVersionNumber()`, which the RFC6455 interface declares as `int`, so every successful
 * WebSocket handshake trips that deprecation. Ratchet supplies the response factory the negotiator
 * builds from, so normalising here keeps the int from ever reaching guzzle.
 *
 * Only reachable with ratchet/rfc6455 ^0.4 (PHP 7.4+, guzzlehttp/psr7 ^2), which is the only
 * version that accepts a response factory. On ratchet/rfc6455 ^0.3 the negotiator constructs its
 * own response and there is no injection point, so that path is unaffected.
 *
 * @internal used internally only, should not be referenced directly
 * @see StringHeaderResponseFactory
 */
class StringHeaderResponse extends Response {
    /**
     * {@inheritdoc}
     */
    public function withHeader($header, $value): MessageInterface {
        return parent::withHeader($header, self::stringifyHeaderValue($value));
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($header, $value): MessageInterface {
        return parent::withAddedHeader($header, self::stringifyHeaderValue($value));
    }

    /**
     * Cast the values guzzle deprecates (any non-string scalar, plus null) to string.
     * Anything else is handed through untouched so guzzle still validates it.
     *
     * @param  mixed $value
     * @return mixed
     */
    private static function stringifyHeaderValue($value) {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                if (self::needsCast($item)) {
                    $value[$key] = (string)$item;
                }
            }

            return $value;
        }

        return self::needsCast($value) ? (string)$value : $value;
    }

    /**
     * @param  mixed $value
     * @return bool
     */
    private static function needsCast($value) {
        return !is_string($value) && (is_scalar($value) || null === $value);
    }
}
