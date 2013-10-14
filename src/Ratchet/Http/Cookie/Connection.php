<?php

namespace Ratchet\Http\Cookie;

use Ratchet\AbstractConnectionDecorator;

use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Cookie\Cookie;

/**
 * Http cookie connection
 */
class Connection extends AbstractConnectionDecorator
{
    /**
     * {@inheritdoc}
     */
    public function send($data)
    {
        if ($data instanceof Response) {
            if (isset($this->wrappedConn->Cookie)) {
                $data->setHeader(
                    'Set-Cookie',
                    $this->renderCookie($this->wrappedConn->Cookie)
                );
            }
        }
        
        $this->getConnection()->send($data);

        return $this;
    }

    /**
     * Render cookie
     * 
     * @param \Guzzle\Plugin\Cookie\Cookie $cookie
     * @return string
     */
    protected function renderCookie(Cookie $cookie)
    {
        return $cookie->getName() . '=' . $cookie->getValue();
    }
    
    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->getConnection()->close();
    }
}
