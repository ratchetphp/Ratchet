<?php

namespace Ratchet\Application\Server;
use Ratchet\Server\IoConnection;

/**
 * @covers Ratchet\Server\IoConnection
 */
class IoConnectionTest extends \PHPUnit_Framework_TestCase {
    protected $sock;

    protected $conn;

    #[\Override]
    public function setUp() {
        $this->sock = $this->getMock(\React\Socket\ConnectionInterface::class);
        $this->conn = new IoConnection($this->sock);
    }

    public function testCloseBubbles(): void {
        $this->sock->expects($this->once())->method('end');
        $this->conn->close();
    }

    public function testSendBubbles(): void {
        $msg = '6 hour rides are productive';

        $this->sock->expects($this->once())->method('write')->with($msg);
        $this->conn->send($msg);
    }

    public function testSendReturnsSelf(): void {
        $this->assertSame($this->conn, $this->conn->send('fluent interface'));
    }
}
