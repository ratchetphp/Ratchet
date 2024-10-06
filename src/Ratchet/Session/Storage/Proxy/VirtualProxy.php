<?php

namespace Ratchet\Session\Storage\Proxy;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

class VirtualProxy extends SessionHandlerProxy {
    /**
     * @var string
     */
    protected $_sessionId;

    /**
     * @var string
     */
    protected $_sessionName;

    public function __construct(\SessionHandlerInterface $handler) {
        parent::__construct($handler);

        $this->saveHandlerName = 'user';
        $this->_sessionName = ini_get('session.name');
    }

    #[\Override]
    public function getId() {
        return $this->_sessionId;
    }

    #[\Override]
    public function setId(string $id) {
        $this->_sessionId = $id;
    }

    #[\Override]
    public function getName() {
        return $this->_sessionName;
    }

    /**
     * DO NOT CALL THIS METHOD
     *
     * @internal
     *
     * @return never
     */
    #[\Override]
    public function setName($name) {
        throw new \RuntimeException("Can not change session name in VirtualProxy");
    }
}
