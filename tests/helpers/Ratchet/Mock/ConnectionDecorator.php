<?php

namespace Ratchet\Mock;
use Override;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\ConnectionInterface;

class ConnectionDecorator extends AbstractConnectionDecorator {
    public array $last = [
        'write' => '',
        'end' => false,
    ];

    #[Override]
    public function send(string $data): ConnectionInterface
    {
        $this->last[__FUNCTION__] = $data;

        $this->getConnection()->send($data);

        return $this;
    }

    #[Override]
    public function close(): void
    {
        $this->last[__FUNCTION__] = true;

        $this->getConnection()->close();
    }
}
