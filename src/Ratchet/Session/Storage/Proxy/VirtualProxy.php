<?php
namespace Ratchet\Session\Storage\Proxy;

use Ratchet\Session\OptionsHandlerInterface;
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

    /**
     * {@inheritdoc}
     */
    public function __construct(\SessionHandlerInterface $handler, OptionsHandlerInterface $optionsHandler) {
        parent::__construct($handler);

        $this->saveHandlerName = 'user';
        $this->_sessionName    = $optionsHandler->get('session.name');
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string {
        return $this->_sessionId;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id): void {
        $this->_sessionId = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string {
        return $this->_sessionName;
    }

    /**
     * DO NOT CALL THIS METHOD
     * @internal
     */
    public function setName($name): void {
        throw new \RuntimeException("Can not change session name in VirtualProxy");
    }
}
