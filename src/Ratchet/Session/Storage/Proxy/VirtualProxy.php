<?php
namespace Ratchet\Session\Storage\Proxy;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

if (PHP_VERSION_ID > 80200 && (new \ReflectionMethod('Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy','setId'))->hasReturnType()) {
    // alias to class for Symfony 7 on PHP 8.2+ using native types like `setId(string $id): void`
    class_alias(__NAMESPACE__ . '\\VirtualProxyForSymfony7', __NAMESPACE__ . '\\VirtualProxy');
} elseif (PHP_VERSION_ID > 80000 && (new \ReflectionMethod('Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy','getId'))->hasReturnType()) {
    // alias to class for Symfony 6 on PHP 8+ using native types like `getId(): string`
    class_alias(__NAMESPACE__ . '\\VirtualProxyForSymfony6', __NAMESPACE__ . '\\VirtualProxy');
} else {
    // fall back to class without native types

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
    public function __construct(\SessionHandlerInterface $handler) {
        parent::__construct($handler);

        $this->saveHandlerName = 'user';
        $this->_sessionName    = ini_get('session.name');
    }

    /**
     * {@inheritdoc}
     */
    public function getId() {
        return $this->_sessionId;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id) {
        $this->_sessionId = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return $this->_sessionName;
    }

    /**
     * DO NOT CALL THIS METHOD
     * @internal
     */
    public function setName($name) {
        throw new \RuntimeException("Can not change session name in VirtualProxy");
    }
}

}
