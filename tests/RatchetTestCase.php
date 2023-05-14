<?php

namespace Ratchet;
use PHPUnit\Framework\TestCase;

class RatchetTestCase extends TestCase {

    private function _version() {
        if (class_exists('PHPUnit_Runner_Version')) {
            return PHPUnit_Runner_Version::id();
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
}
