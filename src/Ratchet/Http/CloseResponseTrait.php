<?php
namespace Ratchet\Http;
use Ratchet\ConnectionInterface;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Response;

trait CloseResponseTrait {
    /**
     * Close a connection with an HTTP response
     * @param \Ratchet\ConnectionInterface $conn
     * @param int                          $code HTTP status code
     * @param bool                         $exposeXPoweredByHeader Exposes to users that Ratchet is installed, through the HTTP header named `X-Powered-By`
     * @return null
     */
    private function close(ConnectionInterface $conn, $code = 400, array $additional_headers = [], $exposeXPoweredByHeader = true) {

        $headers = ($exposeXPoweredByHeader) ? array_merge([
            'X-Powered-By' => \Ratchet\VERSION
        ], $additional_headers) : $additional_headers;

        $response = new Response($code, $headers);
        $conn->send(Message::toString($response));
        $conn->close();
    }
}
