<?php

namespace Ratchet\Server;

use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;

class EchoServerTest extends TestCase
{
    protected ConnectionInterface $connection;

    protected EchoServer $component;

    public function setUp(): void
    {
        $this->connection = $this->createMock(ConnectionInterface::class);
        $this->component = new EchoServer;
    }

    public function testMessageEcho(): void
    {
        $message = 'Tillsonburg, my back still aches when I hear that word.';
        $this->connection->expects($this->once())->method('send')->with($message);
        $this->component->onMessage($this->connection, $message);
    }

    public function testErrorClosesConnection(): void
    {
        ob_start();
        $this->connection->expects($this->once())->method('close');
        $this->component->onError($this->connection, new \Exception);
        ob_end_clean();
    }
}
