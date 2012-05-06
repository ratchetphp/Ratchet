<?php
namespace Ratchet\Tests\Mock;
use Ratchet\Resource\AbstractConnectionDecorator;

class ConnectionDecorator extends AbstractConnectionDecorator {
    public $last = array(
        'write' => ''
      , 'end'   => false
    );

    public function write($data) {
        $this->last[__FUNCTION__] = $data;

        $this->getConnection()->write($data);
    }

    public function end() {
        $this->last[__FUNCTION__] = true;

        $this->getConnection()->end();
    }
}