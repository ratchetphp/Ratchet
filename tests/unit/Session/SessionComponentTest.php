<?php
namespace Ratchet\Session;
use Ratchet\AbstractMessageComponentTestCase;
use Ratchet\Session\SessionProvider;
use Ratchet\Mock\MemorySessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Guzzle\Http\Message\Request;

/**
 * @covers Ratchet\Session\SessionProvider
 * @covers Ratchet\Session\Storage\VirtualSessionStorage
 * @covers Ratchet\Session\Storage\Proxy\VirtualProxy
 */
class SessionProviderTest extends AbstractMessageComponentTestCase {
    public function setUp() {
        if (!class_exists('Symfony\Component\HttpFoundation\Session\Session')) {
            return $this->markTestSkipped('Dependency of Symfony HttpFoundation failed');
        }

        parent::setUp();
        $this->_serv = new SessionProvider($this->_app, new NullSessionHandler);
    }

    public function tearDown() {
        ini_set('session.serialize_handler', 'php');
    }

    public function getConnectionClassString() {
        return '\Ratchet\ConnectionInterface';
    }

    public function getDecoratorClassString() {
        return '\Ratchet\NullComponent';
    }

    public function getComponentClassString() {
        return '\Ratchet\MessageComponentInterface';
    }

    public function classCaseProvider() {
        return array(
            array('php', 'Php')
          , array('php_binary', 'PhpBinary')
        );
    }

    /**
     * @dataProvider classCaseProvider
     */
    public function testToClassCase($in, $out) {
        $ref = new \ReflectionClass('\\Ratchet\\Session\\SessionProvider');
        $method = $ref->getMethod('toClassCase');
        $method->setAccessible(true);

        $component = new SessionProvider($this->getMock('Ratchet\\MessageComponentInterface'), $this->getMock('\SessionHandlerInterface'));
        $this->assertEquals($out, $method->invokeArgs($component, array($in)));
    }

    /**
     * I think I have severely butchered this test...it's not so much of a unit test as it is a full-fledged component test
     */
    public function testConnectionValueFromPdo() {
        if (!extension_loaded('PDO') || !extension_loaded('pdo_sqlite')) {
            return $this->markTestSkipped('Session test requires PDO and pdo_sqlite');
        }

        $sessionId = md5('testSession');

        $dbOptions = array(
            'db_table'    => 'sessions'
          , 'db_id_col'   => 'sess_id'
          , 'db_data_col' => 'sess_data'
          , 'db_time_col' => 'sess_time'
          , 'db_lifetime_col' => 'sess_lifetime'
        );

        $pdo = new \PDO("sqlite::memory:");
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec(vsprintf("CREATE TABLE %s (%s TEXT NOT NULL PRIMARY KEY, %s BLOB NOT NULL, %s INTEGER NOT NULL, %s INTEGER)", $dbOptions));

        $pdoHandler = new PdoSessionHandler($pdo, $dbOptions);
        $pdoHandler->write($sessionId, '_sf2_attributes|a:2:{s:5:"hello";s:5:"world";s:4:"last";i:1332872102;}_sf2_flashes|a:0:{}');

        $component  = new SessionProvider($this->getMock('Ratchet\\MessageComponentInterface'), $pdoHandler, array('auto_start' => 1));
        $connection = $this->getMock('Ratchet\\ConnectionInterface');

        $headers = $this->getMock('Guzzle\\Http\\Message\\Request', array('getCookie'), array('POST', '/', array()));
        $headers->expects($this->once())->method('getCookie', array(ini_get('session.name')))->will($this->returnValue($sessionId));

        $connection->WebSocket          = new \StdClass;
        $connection->WebSocket->request = $headers;

        $component->onOpen($connection);

        $this->assertEquals('world', $connection->Session->get('hello'));
    }

    protected function newConn() {
        $conn = $this->getMock('Ratchet\ConnectionInterface');

        $headers = $this->getMock('Guzzle\Http\Message\Request', array('getCookie'), array('POST', '/', array()));
        $headers->expects($this->once())->method('getCookie', array(ini_get('session.name')))->will($this->returnValue(null));

        $conn->WebSocket          = new \StdClass;
        $conn->WebSocket->request = $headers;

        return $conn;
    }

    public function testOnMessageDecorator() {
        $message = "Database calls are usually blocking  :(";
        $this->_app->expects($this->once())->method('onMessage')->with($this->isExpectedConnection(), $message);
        $this->_serv->onMessage($this->_conn, $message);
    }

    public function testGetSubProtocolsReturnsArray() {
        $mock = $this->getMock('Ratchet\\MessageComponentInterface');
        $comp = new SessionProvider($mock, new NullSessionHandler);

        $this->assertInternalType('array', $comp->getSubProtocols());
    }

    public function testGetSubProtocolsGetFromApp() {
        $mock = $this->getMock('Ratchet\WebSocket\Stub\WsMessageComponentInterface');
        $mock->expects($this->once())->method('getSubProtocols')->will($this->returnValue(array('hello', 'world')));
        $comp = new SessionProvider($mock, new NullSessionHandler);

        $this->assertGreaterThanOrEqual(2, count($comp->getSubProtocols()));
    }

    public function testRejectInvalidSeralizers() {
        if (!function_exists('wddx_serialize_value')) {
            $this->markTestSkipped();
        }

        ini_set('session.serialize_handler', 'wddx');
        $this->setExpectedException('\RuntimeException');
        new SessionProvider($this->getMock('\Ratchet\MessageComponentInterface'), $this->getMock('\SessionHandlerInterface'));
    }
}
