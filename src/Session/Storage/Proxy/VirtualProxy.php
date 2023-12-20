<?php

namespace Ratchet\Session\Storage\Proxy;

use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

class VirtualProxy extends SessionHandlerProxy
{
    protected string $sessionId;

    protected string $sessionName;

    /**
     * {@inheritdoc}
     */
    public function __construct(\SessionHandlerInterface $handler)
    {
        parent::__construct($handler);

        $this->sessionName = ini_get('session.name');
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return $this->sessionId;
    }

    /**
     * {@inheritdoc}
     */
    public function setId(string $id): void
    {
        $this->sessionId = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->sessionName;
    }
}
