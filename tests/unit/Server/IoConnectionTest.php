<?php

namespace Ratchet\Application\Server;

use PHPUnit\Framework\TestCase;
use Ratchet\Server\IoConnection;
use React\Socket\ConnectionInterface;

/**
 * @covers Ratchet\Server\IoConnection
 */
class IoConnectionTest extends TestCase
{
    protected ConnectionInterface $socket;

    protected IoConnection $connection;

    public function setUp(): void
    {
        $this->socket = $this->createMock(ConnectionInterface::class);
        $this->connection = new IoConnection($this->socket);
    }

    public function testCloseBubbles(): void
    {
        $this->socket->expects($this->once())->method('end');
        $this->connection->close();
    }

    public function testSendBubbles(): void
    {
        $message = '6 hour rides are productive';

        $this->socket->expects($this->once())->method('write')->with($message);
        $this->connection->send($message);
    }

    public function testSendReturnsSelf(): void
    {
        $this->assertSame($this->connection, $this->connection->send('fluent interface'));
    }
}
