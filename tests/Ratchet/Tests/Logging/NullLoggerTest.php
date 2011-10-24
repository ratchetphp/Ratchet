<?php
namespace Ratchet\Tests\Logging;
use Ratchet\Logging\NullLogger;

/**
 * @covers Ratchet\Logging\NullLogger
 */
class NullLoggerTest extends \PHPUnit_Framework_TestCase {
    protected $_log;

    public function setUp() {
        $this->_log = new NullLogger;
    }

    public function testInterface() {
        $this->assertInstanceOf('\\Ratchet\\Logging\\LoggerInterface', $this->_log);
    }

    public function testNoteDoesNothing() {
        $this->assertNull($this->_log->note('hi'));
    }

    public function testWarningDoesNothing() {
        $this->assertNull($this->_log->warning('hi'));
    }

    public function testErrorDoesNothing() {
        $this->assertNull($this->_log->error('hi'));
    }
}