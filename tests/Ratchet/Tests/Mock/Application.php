<?php
namespace Ratchet\Tests\Mock;
use Ratchet\Component\ComponentInterface;
use Ratchet\Tests\Mock\Socket as MockSocket;
use Ratchet\Resource\ConnectionInterface;

class Application implements ComponentInterface {
    public $_app;

    public $_conn_open;

    public $_conn_recv;
    public $_msg_recv;

    public $_conn_close;

    public $_conn_error;
    public $_excep_error;

    public function __construct(ComponentInterface $app = null) {
        // probably should make this null app
        $this->_app = $app;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->_conn_open = $conn;
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $this->_conn_recv = $from;
        $this->_msg_recv  = $msg;
    }

    public function onClose(ConnectionInterface $conn) {
        $this->_conn_close = $conn;
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->_conn_error  = $conn;
        $this->_excep_error = $e;
    }
}