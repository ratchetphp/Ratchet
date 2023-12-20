<?php

namespace Ratchet\Session;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Ratchet\AbstractMessageComponentTestCase;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;
use Ratchet\NullComponent;
use RuntimeException;
use SessionHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

/**
 * @covers Ratchet\Session\SessionProvider
 * @covers Ratchet\Session\Storage\VirtualSessionStorage
 * @covers Ratchet\Session\Storage\Proxy\VirtualProxy
 */
class SessionComponentTest extends AbstractMessageComponentTestCase
{
    public function setUp(): void
    {
        $this->markTestIncomplete('Test needs to be updated for ini_set issue in PHP 7.2');

        if (! class_exists(Session::class)) {
            $this->markTestSkipped('Dependency of Symfony HttpFoundation failed');

            return;
        }

        parent::setUp();
        $this->server = new SessionProvider($this->app, new NullSessionHandler);
    }

    public function tearDown(): void
    {
        ini_set('session.serialize_handler', 'php');
    }

    public function getConnectionClassString(): string
    {
        return ConnectionInterface::class;
    }

    public function getDecoratorClassString(): string
    {
        return NullComponent::class;
    }

    public function getComponentClassString(): string
    {
        return HttpServerInterface::class;
    }

    public static function classCaseProvider(): array
    {
        return [
            ['php', 'Php'], ['php_binary', 'PhpBinary'],
        ];
    }

    /**
     * @dataProvider classCaseProvider
     */
    public function testToClassCase($in, $out)
    {
        $ref = new \ReflectionClass(SessionProvider::class);
        $method = $ref->getMethod('toClassCase');
        $method->setAccessible(true);

        $componentMock = $this->getMockBuilder($this->getComponentClassString())->getMock();
        $sessionHandlerMock = $this->getMockBuilder(SessionHandlerInterface::class)->getMock();
        $component = new SessionProvider($componentMock, $sessionHandlerMock);
        $this->assertEquals($out, $method->invokeArgs($component, [$in]));
    }

    /**
     * I think I have severely butchered this test...it's not so much of a unit test as it is a full-fledged component test
     */
    public function testConnectionValueFromPdo(): void
    {
        if (! extension_loaded('PDO') || ! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Session test requires PDO and pdo_sqlite');

            return;
        }

        $sessionId = md5('testSession');

        $dbOptions = [
            'db_table' => 'sessions',
            'db_id_col' => 'id',
            'db_data_col' => 'data',
            'db_time_col' => 'time',
            'db_lifetime_col' => 'lifetime',
        ];

        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec(vsprintf('CREATE TABLE %s (%s TEXT NOT NULL PRIMARY KEY, %s BLOB NOT NULL, %s INTEGER NOT NULL, %s INTEGER)', $dbOptions));

        $pdoHandler = new PdoSessionHandler($pdo, $dbOptions);
        $pdoHandler->write($sessionId, '_sf2_attributes|a:2:{s:5:"hello";s:5:"world";s:4:"last";i:1332872102;}_sf2_flashes|a:0:{}');

        $component = new SessionProvider($this->createMock($this->getComponentClassString()), $pdoHandler, ['auto_start' => 1]);
        $connection = $this->createMock(ConnectionInterface::class);

        $headers = $this->createMock(RequestInterface::class);
        $headers->expects($this->once())->method('getHeader')->will($this->returnValue([ini_get('session.name')."={$sessionId};"]));

        $component->onOpen($connection, $headers);

        $this->assertEquals('world', $connection->Session->get('hello'));
    }

    protected function newConnection(): ConnectionInterface
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $headers = $this->createMock(Request::class, ['getCookie'], ['POST', '/', []]);
        $headers->expects($this->once())->method('getCookie', [ini_get('session.name')])->will($this->returnValue(null));

        return $connection;
    }

    public function testOnMessageDecorator(): void
    {
        $message = 'Database calls are usually blocking  :(';
        $this->app->expects($this->once())->method('onMessage')->with($this->isExpectedConnection(), $message);
        $this->server->onMessage($this->connection, $message);
    }

    public function testRejectInvalidSerializers(): void
    {
        if (! function_exists('wddx_serialize_value')) {
            $this->markTestSkipped();
        }

        ini_set('session.serialize_handler', 'wddx');
        $this->expectException(RuntimeException::class);
        new SessionProvider($this->createMock($this->getComponentClassString()), $this->createMock(SessionHandlerInterface::class));
    }

    protected function doOpen(ConnectionInterface $connection): void
    {
        $request = $this->getMockBuilder(RequestInterface::class)->getMock();
        $request->expects($this->any())->method('getHeader')->will($this->returnValue([]));

        $this->server->onOpen($connection, $request);
    }
}
