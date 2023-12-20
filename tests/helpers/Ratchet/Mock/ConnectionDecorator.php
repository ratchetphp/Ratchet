<?php

namespace Ratchet\Mock;

use Ratchet\AbstractConnectionDecorator;
use Ratchet\ConnectionInterface;

class ConnectionDecorator extends AbstractConnectionDecorator
{
    public array $last = [
        'write' => '',
        'end' => false,
    ];

    public function send(string $data): ConnectionInterface
    {
        $this->last[__FUNCTION__] = $data;

        return $this->getConnection()->send($data);
    }

    public function close(): void
    {
        $this->last[__FUNCTION__] = true;

        $this->getConnection()->close();
    }
}
