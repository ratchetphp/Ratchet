<?php
namespace Ratchet\Tests\Session;
use Ratchet\Session\SessionProvider;
use Ratchet\Tests\Mock\MemorySessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Guzzle\Http\Message\Request;

/**
 * @covers Ratchet\Session\SessionProvider
 * @covers Ratchet\Session\Storage\VirtualSessionStorage
 * @covers Ratchet\Session\Storage\Proxy\VirtualProxy
 */
class SessionProviderTest extends \PHPUnit_Framework_TestCase {
    public function setUp() {
        if (!class_exists('Symfony\\Component\\HttpFoundation\\Session\\Session')) {
            return $this->markTestSkipped('Dependency of Symfony HttpFoundation failed');
        }
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

        $component = new SessionProvider($this->getMock('Ratchet\\MessageComponentInterface'), new MemorySessionHandler);
        $this->assertEquals($out, $method->invokeArgs($component, array($in)));
    }

    /**
     * I think I have severly butchered this test...it's not so much of a unit test as it is a full-fledged component test
     */
    public function testConnectionValueFromPdo() {
        if (!extension_loaded('PDO')) {
            return $this->markTestSkipped('Session test requires PDO');
        }

        $sessionId = md5('testSession');

        $dbOptions = array(
            'db_table'    => 'sessions'
          , 'db_id_col'   => 'sess_id'
          , 'db_data_col' => 'sess_data'
          , 'db_time_col' => 'sess_time'
        );

        $pdo = new \PDO("sqlite::memory:");
        $pdo->exec(vsprintf("CREATE TABLE %s (%s VARCHAR(255) PRIMARY KEY, %s TEXT, %s INTEGER)", $dbOptions));
        $pdo->prepare(vsprintf("INSERT INTO %s (%s, %s, %s) VALUES (?, ?, ?)", $dbOptions))->execute(array($sessionId, base64_encode('_sf2_attributes|a:2:{s:5:"hello";s:5:"world";s:4:"last";i:1332872102;}_sf2_flashes|a:0:{}'), time()));

        $component  = new SessionProvider($this->getMock('Ratchet\\MessageComponentInterface'), new PdoSessionHandler($pdo, $dbOptions), array('auto_start' => 1));
        $connection = $this->getMock('Ratchet\\ConnectionInterface');

        $headers = $this->getMock('Guzzle\\Http\\Message\\Request', array('getCookie'), array('POST', '/', array()));
        $headers->expects($this->once())->method('getCookie', array(ini_get('session.name')))->will($this->returnValue($sessionId));

        $connection->WebSocket          = new \StdClass;
        $connection->WebSocket->request = $headers;

        $component->onOpen($connection);

        $this->assertEquals('world', $connection->Session->get('hello'));
    }

    protected function newConn() {
        $conn = $this->getMock('Ratchet\\ConnectionInterface');

        $headers = $this->getMock('Guzzle\\Http\\Message\\Request', array('getCookie'), array('POST', '/', array()));
        $headers->expects($this->once())->method('getCookie', array(ini_get('session.name')))->will($this->returnValue(null));

        $conn->WebSocket          = new \StdClass;
        $conn->WebSocket->request = $headers;

        return $conn;
    }

    public function testOnOpenBubbles() {
        $conn = $this->newConn();
        $mock = $this->getMock('Ratchet\\MessageComponentInterface');
        $comp = new SessionProvider($mock, new NullSessionHandler);

        $mock->expects($this->once())->method('onOpen')->with($conn);
        $comp->onOpen($conn);
    }

    protected function getOpenConn() {
        $conn = $this->newConn();
        $mock = $this->getMock('Ratchet\\MessageComponentInterface');
        $prov = new SessionProvider($mock, new NullSessionHandler);

        $prov->onOpen($conn);

        return array($conn, $mock, $prov);
    }

    public function testOnMessageBubbles() {
        list($conn, $mock, $prov) = $this->getOpenConn();
        $msg = 'No sessions here';

        $mock->expects($this->once())->method('onMessage')->with($conn, $msg);
        $prov->onMessage($conn, $msg);
    }

    public function testOnCloseBubbles() {
        list($conn, $mock, $prov) = $this->getOpenConn();

        $mock->expects($this->once())->method('onClose')->with($conn);
        $prov->onClose($conn);
    }

    public function testOnErrorBubbles() {
        list($conn, $mock, $prov) = $this->getOpenConn();
        $e = new \Exception('I made a boo boo');

        $mock->expects($this->once())->method('onError')->with($conn, $e);
        $prov->onError($conn, $e);
    }

    public function testGetSubProtocolsReturnsArray() {
        $mock = $this->getMock('Ratchet\\MessageComponentInterface');
        $comp = new SessionProvider($mock, new NullSessionHandler);

        $this->assertInternalType('array', $comp->getSubProtocols());
    }

    public function testGetSubProtocolsGetFromApp() {
        $mock = $this->getMock('Ratchet\\Tests\\WebSocket\\Stub\\WsMessageComponentInterface');
        $mock->expects($this->once())->method('getSubProtocols')->will($this->returnValue(array('hello', 'world')));
        $comp = new SessionProvider($mock, new NullSessionHandler);

        $this->assertGreaterThanOrEqual(2, count($comp->getSubProtocols()));
    }
}