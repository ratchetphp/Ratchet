<?php
namespace Ratchet\Tests\Mock;
use Ratchet\Socket as RealSocket;

class Socket extends RealSocket {
    protected $_options = Array();

    public function __construct($domain = null, $type = null, $protocol = null) {
        list($domain, $type, $protocol) = static::getConfig($domain, $type, $protocol);
    }

    public function accept() {
    }

    public function bind($address, $port) {
    }

    public function close() {
    }

    public function get_option($level, $optname) {
        return $this->_options[$level][$optname];
    }

    public function listen($backlog) {
    }

    public function recv($buf, $len, $flags) {
    }

    public function set_option($level, $optname, $optval) {
        if (!is_array($this->_options[$level])) {
            $this->_options[$level] = Array();
        }

        $this->_options[$level][$optname] = $optval;
    }

    public function write($buffer, $length = 0) {
    }
}