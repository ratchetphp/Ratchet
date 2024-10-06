<?php

namespace Ratchet\Server;
use Override;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use React\Socket\ConnectionInterface as ReactConn;
use stdClass;

class IoConnection implements ConnectionInterface
{
    public RequestInterface $httpRequest;
    public ?stdClass $WebSocket = null;
    public int $resourceId;
    public string $remoteAddress;

    public function __construct(protected ReactConn $conn)
    {
    }

    #[Override]
    public function send(string $data): ConnectionInterface
    {
        $this->conn->write($data);

        return $this;
    }

    #[Override]
    public function close(): void
    {
        $this->conn->end();
    }
}
