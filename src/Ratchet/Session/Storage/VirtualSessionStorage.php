<?php
namespace Ratchet\Session\Storage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Ratchet\Session\Storage\Proxy\VirtualProxy;
use Ratchet\Session\Serialize\HandlerInterface;

class VirtualSessionStorage extends NativeSessionStorage {
    /**
     * @var \Ratchet\Session\Serialize\HandlerInterface
     */
    protected $_serializer;

    /**
     * @param \SessionHandlerInterface                    $handler
     * @param string                                      $sessionId The ID of the session to retrieve
     * @param \Ratchet\Session\Serialize\HandlerInterface $serializer
     */
    public function __construct(\SessionHandlerInterface $handler, $sessionId, HandlerInterface $serializer) {
        $this->setSaveHandler($handler);
        $this->saveHandler->setId($sessionId);
        $this->_serializer = $serializer;
        $this->setMetadataBag(null);
    }

    /**
     * {@inheritdoc}
     */
    public function start() {
        if ($this->started && !$this->closed) {
            return true;
        }

        /** 
         * Open the sessionfile first to avoid a PHP warning
         * PHP Warning:  SessionHandler::read(): Parent session handler is not open
         */
        $this->saveHandler->open(ini_get('session.save_path'), $this->saveHandler->getId());
        // Read the data from the file
        $rawData     = $this->saveHandler->read($this->saveHandler->getId());
        /** Close the file so we wont have a write lock
         * This would hang every other request of the client when not using the websocket server
         */
        $this->saveHandler->close();
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
    public function regenerate($destroy = false, $lifetime = null) {
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

        if (!($saveHandler instanceof VirtualProxy)) {
            $saveHandler = new VirtualProxy($saveHandler);
        }

        $this->saveHandler = $saveHandler;
    }
}
