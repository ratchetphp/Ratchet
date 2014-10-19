<?php

namespace Ratchet;

use Ratchet\Http\HttpServerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * The version of Ratchet being used
 * @var string
 */
const VERSION = 'Ratchet/0.3.2';

/**
 * A proxy object representing a connection to the application
 * This acts as a container to store data (in memory) about the connection
 * @property Session $Session
 * @property bool $httpHeadersReceived
 * @property HttpServerInterface $controller
 * @property string $remoteAddress
 * @property $WAMP
 * @property $WebSocket
 */
interface ConnectionInterface {
    /**
     * Send data to the connection
     * @param  string $data
     * @return \Ratchet\ConnectionInterface
     */
    function send($data);

    /**
     * Close the connection
     */
    function close();
}
