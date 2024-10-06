<?php

namespace Ratchet\Mock;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;

class Connection implements ConnectionInterface {
    public $last = [
        'send' => '',
        'close' => false,
    ];

    public $remoteAddress = '127.0.0.1';

    public RequestInterface $httpRequest;

    protected $WebSocket;

    #[\Override]
    public function send($data) {
        $this->last[__FUNCTION__] = $data;
    }

    #[\Override]
    public function close() {
        $this->last[__FUNCTION__] = true;
    }
}
