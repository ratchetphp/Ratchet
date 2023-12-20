<?php

namespace Ratchet\Mock;

use Ratchet\ConnectionInterface;

class Connection implements ConnectionInterface
{
    public $last = [
        'send' => '',
        'close' => false,
    ];

    public string $remoteAddress = '127.0.0.1';

    public function send(string $data): ConnectionInterface
    {
        $this->last[__FUNCTION__] = $data;

        return $this;
    }

    public function close(): void
    {
        $this->last[__FUNCTION__] = true;
    }
}
