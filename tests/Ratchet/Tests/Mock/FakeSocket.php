<?php
namespace Ratchet\Tests\Mock;
use Ratchet\SocketInterface;
use Ratchet\Socket as RealSocket;

class FakeSocket implements SocketInterface {
    public $_arguments = array();
    public $_options   = array();

    protected $_id = 1;

    public $_last = array();

    public function getResource() {
        return null;
    }

    public function __toString() {
        return (string)$this->_id;
    }

    public function __construct($domain = null, $type = null, $protocol = null) {
        list($this->_arguments['domain'], $this->_arguments['type'], $this->_arguments['protocol']) = array(1, 1, 1);
    }

    public function __clone() {
        $this->_id++;
    }

    public function deliver($message) {
        $this->write($message, strlen($message));
    }

    public function bind($address, $port = 0) {
        $this->_last['bind'] = array($address, $port);
        return $this;
    }

    public function close() {
    }

    public function connect($address, $port = 0) {
        $this->_last['connect'] = array($address, $port = 0);
        return $this;
    }

    public function getRemoteAddress() {
        return '127.0.0.1';
    }

    public function get_option($level, $optname) {
        return $this->_options[$level][$optname];
    }

    public function listen($backlog = 0) {
        $this->_last['listen'] = array($backlog);
        return $this;
    }

    public function read($length, $type = PHP_BINARY_READ) {
        $this->_last['read'] = array($length, $type);
        return 0;
    }

    public function recv(&$buf, $len, $flags) {
        $this->_last['recv'] = array($buf, $len, $flags);
        return 0;
    }

    public function select(&$read, &$write, &$except, $tv_sec, $tv_usec = 0) {
        $this->_last['select'] = array($read, $write, $except, $tv_sec, $tv_usec);
        return 0;
    }

    public function set_block() {
        return $this;
    }

    public function set_nonblock() {
        return $this;
    }

    public function set_option($level, $optname, $optval) {
        if (!isset($this->_options[$level])) {
            $this->_options[$level] = array();
        }

        $this->_options[$level][$optname] = $optval;
    }

    public function shutdown($how = 2) {
        $this->_last['shutdown'] = array($how);
        return $this;
    }

    public function write($buffer, $length = 0) {
        $this->_last['write'] = array($buffer, $length);
        return $this;
    }
}