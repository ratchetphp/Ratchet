<?php
namespace Ratchet\Component\Session\Storage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Ratchet\Component\Session\Storage\Proxy\VirtualProxy;

class VirtualSessionStorage extends NativeSessionStorage {
    /**
     * {@inheritdoc}
     */
    public function __construct(\SessionHandlerInterface $handler, $id) {
        $this->setSaveHandler($handler, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function start() {
        if ($this->started && !$this->closed) {
            return true;
        }

        $ignore = array();
        $this->loadSession($ignore);

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
//        $this->saveHandler->write($this->saveHandler->getId(), 

        if (!$this->saveHandler->isWrapper() && !$this->getSaveHandler()->isSessionHandlerInterface()) {
            $this->saveHandler->setActive(false);
        }

        $this->closed = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setSaveHandler(\SessionHandlerInterface $saveHandler, $id) {
        if (!($saveHandler instanceof \VirtualProxy)) {
            $saveHandler = new VirtualProxy($saveHandler, $id);
        }

        $this->saveHandler = $saveHandler;
    }
}