<?php

namespace Ratchet\Wamp2\Router;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp2\WampClientInterface;

interface WampClientProxyInterface extends ConnectionInterface, WampClientInterface
{

}