<?php
namespace Ratchet\WebSocket;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * [internal] Builds the responses `Ratchet\RFC6455\Handshake\ServerNegotiator` returns.
 *
 * Substituted for `GuzzleHttp\Psr7\HttpFactory` so the negotiator's handshake response tolerates
 * the `int` it sets `Sec-WebSocket-Version` to without raising a guzzlehttp/psr7 deprecation.
 *
 * @internal used internally only, should not be referenced directly
 * @see StringHeaderResponse
 */
class StringHeaderResponseFactory implements ResponseFactoryInterface {
    /**
     * {@inheritdoc}
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface {
        return new StringHeaderResponse($code, [], null, '1.1', $reasonPhrase);
    }
}
