<?php

namespace Ratchet\Http;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Response;
use Ratchet\ConnectionInterface;

trait CloseResponseTrait {
    /**
     * Close a connection with an HTTP response
     *
     * @param int                          $code HTTP status code
     */
    private function close(ConnectionInterface $conn, $code = 400, array $additional_headers = []): void {
        $response = new Response($code, array_merge([
            'X-Powered-By' => \Ratchet\VERSION,
        ], $additional_headers));

        $conn->send(Message::toString($response));
        $conn->close();
    }
}
