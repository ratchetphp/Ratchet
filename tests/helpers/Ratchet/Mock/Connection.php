<?php
namespace Ratchet\Mock;
use Ratchet\ConnectionInterface;

class Connection implements ConnectionInterface {
    public $last = array(
        'send'  => ''
      , 'close' => false
    );

    public $WAMP;

    public $remoteAddress = '127.0.0.1';

    public function send($data) {
        $this->last[__FUNCTION__] = $data;
    }

    public function close() {
        $this->last[__FUNCTION__] = true;
    }

    public function __construct() {
        $this->WAMP            = new \StdClass;
        $this->WAMP->sessionId = str_replace('.', '', uniqid(mt_rand(), true));
        $this->WAMP->prefixes  = array();
    }
}
