<?php
namespace Ratchet;

class Socket {
    protected $_socket;

    public static $_defaults = Array(
        'domain'   => AF_INET
      , 'type'     => SOCK_STREAM
      , 'protocol' => SOL_TCP
    );

    public function __construct($domain = null, $type = null, $protocol = null) {
        foreach (static::$_default as $key => $val) {
            if (null === $$key) {
                $$key = $val;
            }
        }

        $this->_socket = socket_create($domain, $type, $protocol);
    }

    public function __call($method, $arguments) {
        if (function_exists('socket_' . $method)) {
            array_unshift($arguments, $this->_socket);
            return call_user_func_array('socket_' . $method, $arguments);
        }
    }
}