<?php
namespace Ratchet\Mock;
use Ratchet\AbstractConnectionDecorator;

class ConnectionDecorator extends AbstractConnectionDecorator {
    public $last = array(
        'write' => ''
      , 'end'   => false
    );

    public function send($data) {
        $this->last[__FUNCTION__] = $data;

        $this->getConnection()->send($data);
    }

    public function close() {
        $this->last[__FUNCTION__] = true;

        $this->getConnection()->close();
    }
}
