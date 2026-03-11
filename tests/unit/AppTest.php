<?php
namespace Ratchet;

use PHPUnit\Framework\TestCase;
use Ratchet\App;
use Ratchet\Server\IoServer;

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

    public function testCtorWithoutArgumentsStartsListeningOnDefaultPorts() {
        if (@stream_socket_server('127.0.0.1:8080') === false || @stream_socket_server('127.0.0.1:8843') === false) {
            $this->markTestSkipped('Default socket port 8080 or 8843 not available or already in use');
        }
        $app = new App();

        $ref = new \ReflectionProperty($app, '_server');
        // $ref->setAccessible(true);
        $server = $ref->getValue($app);
        assert($server instanceof IoServer);

        $this->assertStringMatchesFormat('%S127.0.0.1:8080', $server->socket->getAddress());
        $this->assertStringMatchesFormat('%S127.0.0.1:8843', $app->flashServer->socket->getAddress());

        $server->socket->close();
        $app->flashServer->socket->close();
    }
}
