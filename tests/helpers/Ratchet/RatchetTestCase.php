<?php

namespace Ratchet;
use PHPUnit\Framework\TestCase;

class RatchetTestCase extends TestCase {

    private function _version() {
        if (class_exists('\PHPUnit_Runner_Version')) {
            return \PHPUnit_Runner_Version::id();
        } else {
            return \PHPUnit\Runner\Version::id();
        }
    }

    public function _getMock() {
        $params = func_get_args();
        if ($this->_version() < 9) {
            return call_user_func_array([$this, 'getMock'], $params);
        } else {
            return call_user_func_array([$this, 'createMock'], $params);
        }
    }

    public function _setExpectedException() {
        $num_params = func_num_args();
        $params = func_get_args();
        if ($this->_version() < 9) {
            call_user_func_array([$this, 'setExpectedException'], $params);
        } else {
            call_user_func_array([$this, 'expectException'], $params);
        }
    }
}
