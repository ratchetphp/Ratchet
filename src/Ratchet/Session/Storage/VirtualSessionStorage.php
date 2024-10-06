<?php

namespace Ratchet\Session\Storage;
use Ratchet\Session\Storage\Proxy\VirtualProxy;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class VirtualSessionStorage extends NativeSessionStorage {
    /**
     * @param string                                      $sessionId The ID of the session to retrieve
     */
    public function __construct(
        \SessionHandlerInterface $handler,
        $sessionId,
        protected \Ratchet\Session\Serialize\HandlerInterface $_serializer
    ) {
        $this->setSaveHandler($handler);
        $this->saveHandler->setId($sessionId);
        $this->setMetadataBag(null);
    }

    /**
     * @return true
     */
    #[\Override]
    public function start() {
        if ($this->started && ! $this->closed) {
            return true;
        }

        // You have to call Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler::open() to use
        // pdo_sqlite (and possible pdo_*) as session storage, if you are using a DSN string instead of a \PDO object
        // in the constructor. The method arguments are filled with the values, which are also used by the symfony
        // framework in this case. This must not be the best choice, but it works.
        $this->saveHandler->open(session_save_path(), session_name());

        $rawData = $this->saveHandler->read($this->saveHandler->getId());
        $sessionData = $this->_serializer->unserialize($rawData);

        $this->loadSession($sessionData);

        if (! $this->saveHandler->isWrapper() && ! $this->saveHandler->isSessionHandlerInterface()) {
            $this->saveHandler->setActive(false);
        }

        return true;
    }

    #[\Override]
    public function regenerate($destroy = false, $lifetime = null) {
        // .. ?
    }

    #[\Override]
    public function save() {
        // get the data from the bags?
        // serialize the data
        // save the data using the saveHandler
//        $this->saveHandler->write($this->saveHandler->getId(),

        if (! $this->saveHandler->isWrapper() && ! $this->getSaveHandler()->isSessionHandlerInterface()) {
            $this->saveHandler->setActive(false);
        }

        $this->closed = true;
    }

    #[\Override]
    public function setSaveHandler(\SessionHandlerInterface|null $saveHandler = null) {
        if (! ($saveHandler instanceof \SessionHandlerInterface)) {
            throw new \InvalidArgumentException('Handler must be instance of SessionHandlerInterface');
        }

        if (! ($saveHandler instanceof VirtualProxy)) {
            $saveHandler = new VirtualProxy($saveHandler);
        }

        $this->saveHandler = $saveHandler;
    }
}
