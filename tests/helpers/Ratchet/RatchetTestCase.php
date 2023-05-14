<?php

namespace Ratchet;
use PHPUnit\Framework\TestCase;

class RatchetTestCase extends TestCase {

    protected function _version() {
        if (class_exists('\PHPUnit_Runner_Version')) {
            return \PHPUnit_Runner_Version::id();
        } else {
            return \PHPUnit\Runner\Version::id();
        }
    }

    public function _getMock() {
        $params = func_get_args();
        if ($this->_version() < 6) {
            return call_user_func_array([$this, 'getMock'], $params);
        } else {
            return call_user_func_array([$this, 'createMock'], $params);
        }
    }

    public function _setExpectedException($exception) {
        if ($this->_version() < 6) {
            call_user_func_array([$this, 'setExpectedException'], [$exception]);
        } else {
            if (substr($exception, 0, 17) === 'PHPUnit_Framework') {
                $exception = str_replace('_', '\\', $exception);
            }
            call_user_func_array([$this, 'expectException'], [$exception]);
        }
    }
}
