<?php

namespace Ratchet\Http;

use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Response;
use Ratchet\ConnectionInterface;

trait CloseResponseTrait
{
    /**
     * Close a connection with an HTTP response
     *
     * @param  int  $code HTTP status code
     */
    private function close(ConnectionInterface $connection, int $code = 400, array $additionalHeaders = []): void
    {
        $response = new Response($code, array_merge([
            'X-Powered-By' => 'Ratchet/0.4.4',
        ], $additionalHeaders));

        $connection->send(Message::toString($response));
        $connection->close();
    }
}
