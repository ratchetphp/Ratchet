<?php
namespace Ratchet\Mock;
use Ratchet\ConnectionInterface;

class Connection implements ConnectionInterface {
    public $last = array(
        'send'  => ''
      , 'close' => false
    );

    public $remoteAddress = '127.0.0.1';

    public function send($data) {
        $this->last[__FUNCTION__] = $data;
    }

    public function close() {
        $this->last[__FUNCTION__] = true;
    }

    public function get($id) {
        throw new \Ratchet\ConnectionPropertyNotFoundException('Mock has no properties');
    }

    public function has($id) {
        return false;
    }
}
