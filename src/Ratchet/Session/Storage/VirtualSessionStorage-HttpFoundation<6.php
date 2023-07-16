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

        // You have to call Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler::open() to use
        // pdo_sqlite (and possible pdo_*) as session storage, if you are using a DSN string instead of a \PDO object
        // in the constructor. The method arguments are filled with the values, which are also used by the symfony
        // framework in this case. This must not be the best choice, but it works.
        $this->saveHandler->open(session_save_path(), session_name());

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
