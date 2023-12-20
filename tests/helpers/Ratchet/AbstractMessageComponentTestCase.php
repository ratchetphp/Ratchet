<?php

namespace Ratchet;

use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

abstract class AbstractMessageComponentTestCase extends TestCase
{
    protected $app;

    protected $server;

    protected $connection;

    abstract public function getConnectionClassString(): string;

    abstract public function getDecoratorClassString(): string;

    abstract public function getComponentClassString(): string;

    public function setUp(): void
    {
        $this->app = $this->createMock($this->getComponentClassString());
        $decorator = $this->getDecoratorClassString();
        $this->server = new $decorator($this->app);
        $this->connection = $this->createMock(ConnectionInterface::class);
        $this->server->onOpen($this->connection);
    }

    public function isExpectedConnection(): IsInstanceOf
    {
        return new IsInstanceOf($this->getConnectionClassString());
    }

    public function testOpen()
    {
        $this->app->expects($this->once())->method('onOpen')->with($this->isExpectedConnection());
        $this->server->onOpen($this->createMock(ConnectionInterface::class));
    }

    public function testOnClose()
    {
        $this->app->expects($this->once())->method('onClose')->with($this->isExpectedConnection());
        $this->server->onClose($this->connection);
    }

    public function testOnError()
    {
        $exception = new \Exception('Whoops!');
        $this->app->expects($this->once())->method('onError')->with($this->isExpectedConnection(), $exception);
        $this->server->onError($this->connection, $exception);
    }

    public function passthroughMessageTest($value)
    {
        $this->app->expects($this->once())->method('onMessage')->with($this->isExpectedConnection(), $value);
        $this->server->onMessage($this->connection, $value);
    }
}
