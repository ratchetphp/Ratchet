<?php
namespace Ratchet\Tests\Mock;
use Ratchet\Resource\ConnectionInterface;

class Connection implements ConnectionInterface {
    public $remoteAddress = '127.0.0.1';
}