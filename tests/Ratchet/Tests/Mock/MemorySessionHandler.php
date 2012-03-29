<?php
namespace Ratchet\Tests\Mock;

class MemorySessionHandler implements \SessionHandlerInterface {
    protected $_sessions = array();

    public function close() {
    }

    public function destroy($session_id) {
        if (isset($this->_sessions[$session_id])) {
            unset($this->_sessions[$session_id]);
        }

        return true;
    }

    public function gc($maxlifetime) {
        return true;
    }

    public function open($save_path, $session_id) {
        if (!isset($this->_sessions[$session_id])) {
            $this->_sessions[$session_id] = '';
        }

        return true;
    }

    public function read($session_id) {
        return $this->_sessions[$session_id];
    }

    public function write($session_id, $session_data) {
        $this->_sessions[$session_id] = $session_data;

        return true;
    }
}