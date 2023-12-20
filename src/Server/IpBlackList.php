<?php

namespace Ratchet\Server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class IpBlackList implements MessageComponentInterface
{
    protected array $blacklist = [];

    protected MessageComponentInterface $decorating;

    public function __construct(MessageComponentInterface $component)
    {
        $this->decorating = $component;
    }

    /**
     * Add an address to the blacklist that will not be allowed to connect to your application
     *
     * @param  string  $ip IP address to block from connecting to your application
     */
    public function blockAddress(string $ip): self
    {
        $this->blacklist[$ip] = true;

        return $this;
    }

    /**
     * Unblock an address so they can access your application again
     *
     * @param  string  $ip IP address to unblock from connecting to your application
     */
    public function unblockAddress(string $ip): self
    {
        if (isset($this->blacklist[$this->filterAddress($ip)])) {
            unset($this->blacklist[$this->filterAddress($ip)]);
        }

        return $this;
    }

    public function isBlocked(string $address): bool
    {
        return isset($this->blacklist[$this->filterAddress($address)]);
    }

    /**
     * Get an array of all the addresses blocked
     */
    public function getBlockedAddresses(): array
    {
        return array_keys($this->blacklist);
    }

    public function filterAddress(string $address): string
    {
        if (strstr($address, ':') && substr_count($address, '.') == 3) {
            [$address, $port] = explode(':', $address);
        }

        return $address;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection)
    {
        if ($this->isBlocked($connection->remoteAddress)) {
            return $connection->close();
        }

        return $this->decorating->onOpen($connection);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, string $message)
    {
        return $this->decorating->onMessage($connection, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        if (! $this->isBlocked($connection->remoteAddress)) {
            $this->decorating->onClose($connection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $exception)
    {
        if (! $this->isBlocked($connection->remoteAddress)) {
            $this->decorating->onError($connection, $exception);
        }
    }
}
