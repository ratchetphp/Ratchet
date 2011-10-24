<?php
namespace Ratchet\Tests\Mock;
use Ratchet\SocketAggregator as RealSocketAggregator;

class SocketAggregator extends RealSocketAggregator {
    protected $_arguments = Array();
    protected $_options   = Array();

    public function __construct($domain = null, $type = null, $protocol = null) {
        list($this->_arguments['domain'], $this->_arguments['type'], $this->_arguments['protocol']) = static::getConfig($domain, $type, $protocol);
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
        if (!isset($this->_options[$level])) {
            $this->_options[$level] = Array();
        }

        $this->_options[$level][$optname] = $optval;
    }

    public function write($buffer, $length = 0) {
    }
}