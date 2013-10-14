<?php

namespace Ratchet\Http\Cookie;

use Ratchet\MessageComponentInterface;
use Ratchet\Http\HttpServerInterface;
use Ratchet\ConnectionInterface;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Plugin\Cookie\Cookie;

/**
 * Http cookie handler
 */
abstract class Handler implements HttpServerInterface
{
    /**
     * Decorated component
     * 
     * @var \Ratchet\MessageComponentInterface
     */
    protected $component;

    /**
     * Cookie
     * 
     * @var \Guzzle\Plugin\Cookie\Cookie
     */
    protected $cookie;
    
    /**
     * Constructor
     * 
     * @param \Ratchet\MessageComponentInterface $component
     * @param \Guzzle\Plugin\Cookie\Cookie       $cookie
     */
    public function __construct(MessageComponentInterface $component, Cookie $cookie)
    {
        // Set decorated component
        $this->component = $component;
        
        // Set cookie
        $this->cookie = $cookie;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null)
    {
        if (!$request) {
            throw new \UnexpectedValueException('$request can not be null');
        }

        // Get cookie value
        $cookieValue = $request->getCookie($this->cookie->getName());
        
        if (null !== $cookieValue) {
            // Set if exists
            $this->cookie->setValue($cookieValue);
        } else {
            // Generate if not exists
            $this->cookie->setValue(
                (string) $this->generateCookieValue()
            );
            
            // Wrap new connection to set cookie value on response
            $conn = new Connection($conn);
        }
        
        // Clone cookie and attach to connection
        $conn->Cookie = clone $this->cookie;
        
        // Cascading...
        return $this->component->onOpen($conn, $request);
    }

    /**
     * Generate cookie value
     * 
     * @return string
     */
    abstract public function generateCookieValue();
    
    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        return $this->component->onMessage($from, $msg);
    }
    
    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn)
    {
        return $this->component->onClose($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        return $this->component->onError($conn, $e);
    }
}
