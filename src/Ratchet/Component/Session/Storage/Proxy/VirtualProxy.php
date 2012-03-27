<?php
namespace Ratchet\Component\Session\Storage\Proxy;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

class VirtualProxy extends SessionHandlerProxy {
    protected $_sessionId;
    protected $_sessionName;

    /**
     * {@inheritdoc}
     */
    public function __construct(\SessionHandlerInterface $handler, $sessionId) {
        parent::__construct($handler);

        $this->saveHandlerName = 'user';
        $this->_sessionId      = $sessionId;
        $this->_sessionName    = ini_get('session.name');
    }

    /**
     * {@inheritdoc}
     */
    public function getId() {
        return $this->_sessionId;
    }

    public function setId($id) {
        throw new \RuntimeException("Can not change session id in VirtualProxy");
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return $this->_sessionName;
    }

    /**
     * DO NOT CALL THIS METHOD  
     * @param string
     * @throws RuntimeException
     */
    public function setName($name) {
        throw new \RuntimeException("Can not change session name in VirtualProxy");
    }
}