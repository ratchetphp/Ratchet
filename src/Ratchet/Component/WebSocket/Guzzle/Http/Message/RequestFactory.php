<?php
namespace Ratchet\Component\WebSocket\Guzzle\Http\Message;
use Guzzle\Http\Message\RequestFactory as gReqFac;
use Guzzle\Http\Url;

/**
 * Just slighly changing the Guzzle RequestFactory to always return an EntityEnclosingRequest instance instead of Request
 */
class RequestFactory extends gReqFac {
    public static function getInstance() {
        static $instance = null;
        if (null === $instance) {
            $instance = parent::getInstance();
            static::$instance->requestClass = static::$instance->entityEnclosingRequestClass;
        }

        return $instance;
    }
}