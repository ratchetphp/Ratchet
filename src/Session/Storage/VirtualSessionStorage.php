<?php

namespace Ratchet\Session\Storage;

use Ratchet\Session\Serialize\HandlerInterface;
use Ratchet\Session\Storage\Proxy\VirtualProxy;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class VirtualSessionStorage extends NativeSessionStorage
{
    /**
     * @var \Ratchet\Session\Serialize\HandlerInterface
     */
    protected $serializer;

    /**
     * @param  string  $sessionId The ID of the session to retrieve
     */
    public function __construct(\SessionHandlerInterface $handler, $sessionId, HandlerInterface $serializer)
    {
        $this->setSaveHandler($handler);
        $this->saveHandler->setId($sessionId);
        $this->serializer = $serializer;
        $this->setMetadataBag(null);
    }

    /**
     * {@inheritdoc}
     */
    public function start(): bool
    {
        if ($this->started && ! $this->closed) {
            return true;
        }

        // You have to call Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler::open() to use
        // pdo_sqlite (and possible pdo_*) as session storage, if you are using a DSN string instead of a \PDO object
        // in the constructor. The method arguments are filled with the values, which are also used by the symfony
        // framework in this case. This must not be the best choice, but it works.
        $this->saveHandler->open(session_save_path(), session_name());

        $rawData = $this->saveHandler->read($this->saveHandler->getId());
        $sessionData = $this->serializer->unserialize($rawData);

        $this->loadSession($sessionData);

        if (! $this->saveHandler->isWrapper() && ! $this->saveHandler->isSessionHandlerInterface()) {
            $this->saveHandler->setActive(false);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function regenerate($destroy = false, $lifetime = null): bool
    {
        return parent::regenerate($destroy, $lifetime);
    }

    /**
     * {@inheritdoc}
     */
    public function save(): void
    {
        // get the data from the bags?
        // serialize the data
        // save the data using the saveHandler
        //        $this->saveHandler->write($this->saveHandler->getId(),

        if (! $this->saveHandler->isWrapper() && ! $this->getSaveHandler()->isSessionHandlerInterface()) {
            $this->saveHandler->setActive(false);
        }

        $this->closed = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setSaveHandler($saveHandler = null): void
    {
        if (! ($saveHandler instanceof \SessionHandlerInterface)) {
            throw new \InvalidArgumentException('Handler must be instance of SessionHandlerInterface');
        }

        if (! ($saveHandler instanceof VirtualProxy)) {
            $saveHandler = new VirtualProxy($saveHandler);
        }

        $this->saveHandler = $saveHandler;
    }
}
