<?php
namespace Ratchet\Component\Session\Storage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Ratchet\Component\Session\Storage\Proxy\VirtualProxy;
use Ratchet\Component\Session\Serialize\HandlerInterface;

class VirtualSessionStorage extends NativeSessionStorage {
    /**
     * @var Ratchet\Component\Session\Serialize\HandlerInterface
     */
    protected $_serializer;

    /**
     * {@inheritdoc}
     */
    public function __construct(\SessionHandlerInterface $handler, $sessionId, HandlerInterface $serializer) {
        $this->setSaveHandler($handler);
        $this->saveHandler->setId($sessionId);
        $this->_serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function start() {
        if ($this->started && !$this->closed) {
            return true;
        }

        $rawData     = $this->saveHandler->read($this->saveHandler->getId());
        $sessionData = $this->_serializer->unserialize($rawData);

        $this->loadSession($sessionData);

        if (!$this->saveHandler->isWrapper() && !$this->saveHandler->isSessionHandlerInterface()) {
            $this->saveHandler->setActive(false);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function regenerate($destroy = false) {
        // .. ?
    }

    /**
     * {@inheritdoc}
     */
    public function save() {
        // get the data from the bags?
        // serialize the data
        // save the data using the saveHandler
//        $this->saveHandler->write($this->saveHandler->getId(), 

        if (!$this->saveHandler->isWrapper() && !$this->getSaveHandler()->isSessionHandlerInterface()) {
            $this->saveHandler->setActive(false);
        }

        $this->closed = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setSaveHandler($saveHandler = null) {
        if (!($saveHandler instanceof \SessionHandlerInterface)) {
            throw new \InvalidArgumentException('Handler must be instance of SessionHandlerInterface');
        }

        if (!($saveHandler instanceof \VirtualProxy)) {
            $saveHandler = new VirtualProxy($saveHandler);
        }

        $this->saveHandler = $saveHandler;
    }
}