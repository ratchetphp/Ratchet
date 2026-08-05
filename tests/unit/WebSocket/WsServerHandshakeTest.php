<?php
namespace Ratchet\WebSocket;
use PHPUnit\Framework\TestCase;
use Ratchet\Mock\Connection;
use Ratchet\NullComponent;

/**
 * @covers Ratchet\WebSocket\WsServer
 * @covers Ratchet\WebSocket\StringHeaderResponse
 * @covers Ratchet\WebSocket\StringHeaderResponseFactory
 */
class WsServerHandshakeTest extends TestCase {
    /**
     * The response factory is only injectable with ratchet/rfc6455 ^0.4 on guzzlehttp/psr7 ^2;
     * older combinations have no injection point and are not expected to pass these.
     */
    protected function requireInjectableNegotiator() {
        if (!class_exists('GuzzleHttp\Psr7\HttpFactory') || !interface_exists('Psr\Http\Message\ResponseFactoryInterface')) {
            $this->markTestSkipped('Requires guzzlehttp/psr7 ^2');
        }

        $reflection = new \ReflectionClass('Ratchet\RFC6455\Handshake\ServerNegotiator');
        if ($reflection->getMethod('__construct')->getNumberOfRequiredParameters() === 1) {
            $this->markTestSkipped('Requires ratchet/rfc6455 ^0.4');
        }
    }

    protected function newUpgradeRequest() {
        return new \GuzzleHttp\Psr7\Request('GET', 'ws://localhost/echo', array(
            'Host'                  => 'localhost'
          , 'Upgrade'               => 'websocket'
          , 'Connection'            => 'Upgrade'
          , 'Sec-WebSocket-Key'     => 'x3JJHMbDL1EzLkh9GBhXDw=='
          , 'Sec-WebSocket-Version' => '13'
        ));
    }

    /**
     * guzzlehttp/psr7 2.11+ deprecates non-string header values and 3.0 rejects them outright.
     * The negotiator sets Sec-WebSocket-Version from an int, so without the response factory
     * substituted in WsServer every successful handshake raises a deprecation.
     */
    public function testHandshakeRaisesNoDeprecation() {
        $this->requireInjectableNegotiator();

        $raised = array();
        set_error_handler(function ($errno, $errstr) use (&$raised) {
            $raised[] = $errstr;

            return true;
        }, E_DEPRECATED | E_USER_DEPRECATED);

        try {
            $server = new WsServer(new NullComponent);
            $server->onOpen(new Connection, $this->newUpgradeRequest());
        } catch (\Exception $e) {
            restore_error_handler();
            throw $e;
        }

        restore_error_handler();

        $this->assertSame(array(), $raised, "Handshake raised: " . implode('; ', $raised));
    }

    public function testHandshakeStillSucceeds() {
        $this->requireInjectableNegotiator();

        $conn   = new Connection;
        $server = new WsServer(new NullComponent);
        $server->onOpen($conn, $this->newUpgradeRequest());

        $this->assertStringStartsWith('HTTP/1.1 101', $conn->last['send']);
        $this->assertFalse($conn->last['close']);
    }

    /**
     * A rejected upgrade is the one response that actually carries Sec-WebSocket-Version out to
     * the client, so it is where a mangled cast would be visible on the wire.
     */
    public function testRejectedUpgradeSendsVersionHeaderAsString() {
        $this->requireInjectableNegotiator();

        $request = new \GuzzleHttp\Psr7\Request('GET', 'ws://localhost/echo', array(
            'Host'                  => 'localhost'
          , 'Sec-WebSocket-Key'     => 'x3JJHMbDL1EzLkh9GBhXDw=='
          , 'Sec-WebSocket-Version' => '13'
        ));

        $conn   = new Connection;
        $server = new WsServer(new NullComponent);
        $server->onOpen($conn, $request);

        $this->assertStringStartsWith('HTTP/1.1 426', $conn->last['send']);
        $this->assertTrue(false !== strpos($conn->last['send'], "Sec-WebSocket-Version: 13\r\n"), $conn->last['send']);
    }

    public function testResponseCastsScalarHeaderValuesToString() {
        $this->requireInjectableNegotiator();

        $factory  = new StringHeaderResponseFactory;
        $response = $factory->createResponse();

        $this->assertSame(array('13'), $response->withHeader('Sec-WebSocket-Version', 13)->getHeader('Sec-WebSocket-Version'));
        $this->assertSame(array('1.5'), $response->withHeader('X-Float', 1.5)->getHeader('X-Float'));
        $this->assertSame(array('7', '9'), $response->withHeader('X-List', array(7, 9))->getHeader('X-List'));
        $this->assertSame(array('kept'), $response->withHeader('X-String', 'kept')->getHeader('X-String'));
        $this->assertSame(array('13'), $response->withAddedHeader('X-Added', 13)->getHeader('X-Added'));
    }
}
