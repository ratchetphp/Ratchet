<?php
namespace Ratchet\Tests\Mock;
use Ratchet\Application\ApplicationInterface;
use Ratchet\Tests\Mock\Socket as MockSocket;
use Ratchet\Resource\Connection;

class Application implements ApplicationInterface {
    public $_app;

    public $_conn_open;

    public $_conn_recv;
    public $_msg_recv;

    public $_conn_close;

    public $_conn_error;
    public $_excep_error;

    public function __construct(ApplicationInterface $app = null) {
        // probably should make this null app
        $this->_app = $app;
    }

    public function onOpen(Connection $conn) {
        $this->_conn_open = $conn;
    }

    public function onMessage(Connection $from, $msg) {
        $this->_conn_recv = $from;
        $this->_msg_recv  = $msg;
    }

    public function onClose(Connection $conn) {
        $this->_conn_close = $conn;
    }

    public function onError(Connection $conn, \Exception $e) {
        $this->_conn_error  = $conn;
        $this->_excep_error = $e;
    }
}