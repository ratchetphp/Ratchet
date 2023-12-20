<?php

namespace Ratchet\Server;

use Ratchet\ConnectionInterface;
use React\Socket\ConnectionInterface as ReactConn;

/**
 * {@inheritdoc}
 */
class IoConnection implements ConnectionInterface
{
    public function __construct(
        protected ReactConn $connection,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function send(string $data): ConnectionInterface
    {
        $this->connection->write($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->connection->end();
    }
}
