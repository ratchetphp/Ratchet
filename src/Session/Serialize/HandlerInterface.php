<?php

namespace Ratchet\Session\Serialize;

interface HandlerInterface
{
    public function serialize(array $data): string;

    public function unserialize(string $raw): array;
}
