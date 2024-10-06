<?php

namespace Ratchet\Session;
use Ratchet\AbstractMessageComponentTestCase;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

/**
 * @covers Ratchet\Session\SessionProvider
 * @covers Ratchet\Session\Storage\VirtualSessionStorage
 * @covers Ratchet\Session\Storage\Proxy\VirtualProxy
 */
class SessionProviderTest extends AbstractMessageComponentTestCase {
    #[\Override]
    public function setUp() {
        return $this->markTestIncomplete('Test needs to be updated for ini_set issue in PHP 7.2');

        if (! class_exists(\Symfony\Component\HttpFoundation\Session\Session::class)) {
            return $this->markTestSkipped('Dependency of Symfony HttpFoundation failed');
        }

        parent::setUp();
        $this->_serv = new SessionProvider($this->_app, new NullSessionHandler);
    }

    #[\Override]
    public function tearDown() {
        ini_set('session.serialize_handler', 'php');
    }

    #[\Override]
    public function getConnectionClassString(): string {
        return \Ratchet\ConnectionInterface::class;
    }

    #[\Override]
    public function getDecoratorClassString(): string {
        return \Ratchet\NullComponent::class;
    }

    #[\Override]
    public function getComponentClassString(): string {
        return \Ratchet\Http\HttpServerInterface::class;
    }

    public function classCaseProvider() {
        return [
            ['php', 'Php'], ['php_binary', 'PhpBinary'],
        ];
    }

    /**
     * @dataProvider classCaseProvider
     */
    public function testToClassCase($in, $out): void {
        $ref = new \ReflectionClass(\Ratchet\Session\SessionProvider::class);
        $method = $ref->getMethod('toClassCase');
        $method->setAccessible(true);

        $component = new SessionProvider($this->getMock($this->getComponentClassString()), $this->getMock('\SessionHandlerInterface'));
        $this->assertEquals($out, $method->invokeArgs($component, [$in]));
    }

    /**
     * I think I have severely butchered this test...it's not so much of a unit test as it is a full-fledged component test
     */
    public function testConnectionValueFromPdo() {
        if (! extension_loaded('PDO') || ! extension_loaded('pdo_sqlite')) {
            return $this->markTestSkipped('Session test requires PDO and pdo_sqlite');
        }

        $sessionId = md5('testSession');

        $dbOptions = [
            'db_table' => 'sessions',
            'db_id_col' => 'sess_id',
            'db_data_col' => 'sess_data',
            'db_time_col' => 'sess_time',
            'db_lifetime_col' => 'sess_lifetime',
        ];

        $pdo = new \PDO("sqlite::memory:");
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec(vsprintf("CREATE TABLE %s (%s TEXT NOT NULL PRIMARY KEY, %s BLOB NOT NULL, %s INTEGER NOT NULL, %s INTEGER)", $dbOptions));

        $pdoHandler = new PdoSessionHandler($pdo, $dbOptions);
        $pdoHandler->write($sessionId, '_sf2_attributes|a:2:{s:5:"hello";s:5:"world";s:4:"last";i:1332872102;}_sf2_flashes|a:0:{}');

        $component = new SessionProvider($this->getMock($this->getComponentClassString()), $pdoHandler, [
            'auto_start' => 1,
        ]);
        $connection = $this->getMock(\Ratchet\ConnectionInterface::class);

        $headers = $this->getMock(\Psr\Http\Message\RequestInterface::class);
        $headers->expects($this->once())->method('getHeader')->will($this->returnValue([ini_get('session.name') . "={$sessionId};"]));

        $component->onOpen($connection, $headers);

        $this->assertEquals('world', $connection->Session->get('hello'));
    }

    protected function newConn() {
        $conn = $this->getMock(\Ratchet\ConnectionInterface::class);

        $headers = $this->getMock('Psr\Http\Message\Request', ['getCookie'], ['POST', '/', []]);
        $headers->expects($this->once())->method('getCookie')->will($this->returnValue(null));

        return $conn;
    }

    public function testOnMessageDecorator(): void {
        $message = "Database calls are usually blocking  :(";
        $this->_app->expects($this->once())->method('onMessage')->with($this->isExpectedConnection(), $message);
        $this->_serv->onMessage($this->_conn, $message);
    }

    public function testRejectInvalidSeralizers(): void {
        if (! function_exists('wddx_serialize_value')) {
            $this->markTestSkipped();
        }

        ini_set('session.serialize_handler', 'wddx');
        $this->setExpectedException('\RuntimeException');
        new SessionProvider($this->getMock($this->getComponentClassString()), $this->getMock('\SessionHandlerInterface'));
    }

    #[\Override]
    protected function doOpen($conn) {
        $request = $this->getMock(\Psr\Http\Message\RequestInterface::class);
        $request->expects($this->any())->method('getHeader')->will($this->returnValue([]));

        $this->_serv->onOpen($conn, $request);
    }
}
