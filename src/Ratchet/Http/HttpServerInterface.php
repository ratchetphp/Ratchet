<?php
namespace Ratchet\Http;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Guzzle\Http\Message\RequestInterface;

interface HttpServerInterface extends MessageComponentInterface {
    /**
     * @param \Ratchet\ConnectionInterface          $conn
     * @param \Guzzle\Http\Message\RequestInterface $headers null is default because PHP won't let me overload; don't pass null!!!
     * @return mixed
     */
    public function onOpen(ConnectionInterface $conn, RequestInterface $headers = null);
}
