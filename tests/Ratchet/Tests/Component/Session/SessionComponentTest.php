<?php
namespace Ratchet\Tests\Component\Session;
use Ratchet\Component\Session\SessionComponent;
use Ratchet\Tests\Mock\NullMessageComponent;
use Ratchet\Tests\Mock\MemorySessionHandler;
use Ratchet\Resource\Connection;
use Ratchet\Tests\Mock\FakeSocket;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Guzzle\Http\Message\Request;

/**
 * @covers Ratchet\Component\Session\SessionComponent
 */
class SessionComponentTest extends \PHPUnit_Framework_TestCase {
    /**
     * @return bool
     */
    public function checkSymfonyPresent() {
        return class_exists('Symfony\\Component\\HttpFoundation\\Session\\Session');
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
        if (!interface_exists('SessionHandlerInterface')) {
            return $this->markTestSkipped('SessionHandlerInterface not defined. Requires PHP 5.4 or Symfony HttpFoundation');
        }

        $ref = new \ReflectionClass('\\Ratchet\\Component\\Session\\SessionComponent');
        $method = $ref->getMethod('toClassCase');
        $method->setAccessible(true);

        $component = new SessionComponent(new NullMessageComponent, new MemorySessionHandler);
        $this->assertEquals($out, $method->invokeArgs($component, array($in)));
    }

    /**
     * I think I have severly butchered this test...it's not so much of a unit test as it is a full-fledged component test
     */
    public function testConnectionValueFromPdo() {
        if (false === $this->checkSymfonyPresent()) {
            return $this->markTestSkipped('Dependency of Symfony HttpFoundation failed');
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

        $component  = new SessionComponent(new NullMessageComponent, new PdoSessionHandler($pdo, $dbOptions), array('auto_start' => 1));
        $connection = new Connection(new FakeSocket);

        $headers = $this->getMock('Guzzle\\Http\\Message\\Request', array('getCookie'), array('POST', '/', array()));
        $headers->expects($this->once())->method('getCookie', array(ini_get('session.name')))->will($this->returnValue($sessionId));

        $connection->WebSocket          = new \StdClass;
        $connection->WebSocket->headers = $headers;

        $component->onOpen($connection);

        $this->assertEquals('world', $connection->Session->get('hello'));
    }
}