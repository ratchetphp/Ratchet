<?php
namespace Ratchet\Session\Storage\Proxy;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

/**
 * [internal] VirtualProxy for Symfony 7 on PHP 8.2+ using native types like `setId(string $id): void`
 *
 * @internal used internally only, should not be referenced directly
 * @see VirtualProxy
 */
class VirtualProxyForSymfony7 extends SessionHandlerProxy {
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
    public function __construct(\SessionHandlerInterface $handler) {
        parent::__construct($handler);

        $this->saveHandlerName = 'user';
        $this->_sessionName    = ini_get('session.name');
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
    public function setId(string $id): void {
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
    public function setName(string $name): void {
        throw new \RuntimeException("Can not change session name in VirtualProxy");
    }
}
