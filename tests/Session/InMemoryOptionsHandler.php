<?php

namespace Ratchet\Tests\Session;

use Ratchet\Session\OptionsHandlerInterface;

final class InMemoryOptionsHandler implements OptionsHandlerInterface
{
    /** @var array<string, mixed> */
    private $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function get(string $name) : string
    {
        return $this->options[$name] ?? '';
    }

    public function set(string $name, $value) : void
    {
        $this->options[$name] = $value;
    }
}
