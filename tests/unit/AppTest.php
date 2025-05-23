<?php
namespace Ratchet;

use PHPUnit\Framework\TestCase;
use Ratchet\App;

class AppTest extends TestCase {
    public function testCtorThrowsForInvalidLoop() {
        if (method_exists($this, 'expectException')) {
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionMessage('Argument #4 ($loop) expected null|React\EventLoop\LoopInterface');
        } else {
            $this->setExpectedException('InvalidArgumentException', 'Argument #4 ($loop) expected null|React\EventLoop\LoopInterface');
        }
        new App('localhost', 8080, '127.0.0.1', 'loop');
    }
}
