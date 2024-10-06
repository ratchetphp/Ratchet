<?php

namespace Ratchet\Server;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use React\Socket\ConnectionInterface as ReactConn;

class IoConnection implements ConnectionInterface {
    protected RequestInterface $httpRequest;

    protected $WebSocket;

    public function __construct(
        protected ReactConn $conn
    )
    {
    }

    /**
     * @return static
     */
    #[\Override]
    public function send($data) {
        $this->conn->write($data);

        return $this;
    }

    #[\Override]
    public function close() {
        $this->conn->end();
    }
}
