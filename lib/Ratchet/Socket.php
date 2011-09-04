<?php
namespace Ratchet;

class Socket {
    protected $_socket;

    public function __construct() {
//        $this->_socket = socket_open();
    }

    public function __call($method, $arguments) {
        if (function_exists('socket_' . $method)) {
            array_unshift($arguments, $this->_socket);
            return call_user_func_array('socket_' . $method, $arguments);
        }
    }
}