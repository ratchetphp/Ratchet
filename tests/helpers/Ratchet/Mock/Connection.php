<?php

namespace Ratchet\Mock;

use Override;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use stdClass;

class Connection implements ConnectionInterface
{
    public array $last = [
        'send' => '',
        'close' => false,
    ];

    public string $remoteAddress = '127.0.0.1';
    public RequestInterface $httpRequest;
    public ?stdClass $WebSocket = null;

    #[Override]
    public function send(string $data): ConnectionInterface
    {
        $this->last[__FUNCTION__] = $data;
    }

    #[Override]
    public function close(): void
    {
        $this->last[__FUNCTION__] = true;
    }
}
