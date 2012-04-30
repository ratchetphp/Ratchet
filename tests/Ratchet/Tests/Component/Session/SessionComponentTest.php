<?php
namespace Ratchet\Tests\Component\Session;
use Ratchet\Component\Session\SessionComponent;
use Ratchet\Tests\Mock\Component as MockComponent;
use Ratchet\Tests\Mock\MemorySessionHandler;
use Ratchet\Tests\Mock\Connection;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Guzzle\Http\Message\Request;

/**
 * @covers Ratchet\Component\Session\SessionComponent
 * @covers Ratchet\Component\Session\Storage\VirtualSessionStorage
 * @covers Ratchet\Component\Session\Storage\Proxy\VirtualProxy
 */
class SessionComponentTest extends \PHPUnit_Framework_TestCase {
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
        $ref = new \ReflectionClass('\\Ratchet\\Component\\Session\\SessionComponent');
        $method = $ref->getMethod('toClassCase');
        $method->setAccessible(true);

        $component = new SessionComponent(new MockComponent, new MemorySessionHandler);
        $this->assertEquals($out, $method->invokeArgs($component, array($in)));
    }

    /**
     * I think I have severly butchered this test...it's not so much of a unit test as it is a full-fledged component test
     */
    public function testConnectionValueFromPdo() {
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

        $component  = new SessionComponent(new MockComponent, new PdoSessionHandler($pdo, $dbOptions), array('auto_start' => 1));
        $connection = new Connection();

        $headers = $this->getMock('Guzzle\\Http\\Message\\Request', array('getCookie'), array('POST', '/', array()));
        $headers->expects($this->once())->method('getCookie', array(ini_get('session.name')))->will($this->returnValue($sessionId));

        $connection->WebSocket          = new \StdClass;
        $connection->WebSocket->headers = $headers;

        $component->onOpen($connection);

        $this->assertEquals('world', $connection->Session->get('hello'));
    }

    public function testDecoratingMethods() {
        $conns = array();
        for ($i = 1; $i <= 3; $i++) {
            $conns[$i] = new Connection;

            $headers = $this->getMock('Guzzle\\Http\\Message\\Request', array('getCookie'), array('POST', '/', array()));
            $headers->expects($this->once())->method('getCookie', array(ini_get('session.name')))->will($this->returnValue(null));

            $conns[$i]->WebSocket          = new \StdClass;
            $conns[$i]->WebSocket->headers = $headers;
        }

        $mock = new MockComponent;
        $comp = new SessionComponent($mock, new NullSessionHandler);

        $comp->onOpen($conns[1]);
        $comp->onOpen($conns[3]);
        $comp->onOpen($conns[2]);
        $this->assertSame($conns[2], $mock->last['onOpen'][0]);

        $msg = 'Hello World!';
        $comp->onMessage($conns[1], $msg);
        $this->assertSame($conns[1], $mock->last['onMessage'][0]);
        $this->assertEquals($msg, $mock->last['onMessage'][1]);

        $comp->onClose($conns[3]);
        $this->assertSame($conns[3], $mock->last['onClose'][0]);

        try {
            throw new \Exception('I threw an error');
        } catch (\Exception $e) {
        }

        $comp->onError($conns[2], $e);
        $this->assertEquals($conns[2], $mock->last['onError'][0]);
        $this->assertEquals($e, $mock->last['onError'][1]);
    }
}