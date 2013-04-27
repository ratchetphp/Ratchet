<?php
namespace Ratchet\Tests\Http;
use Ratchet\Tests\AbstractMessageComponentTestCase;

/**
 * @covers Ratchet\Http\HttpServer
 */
class HttpServerTest extends AbstractMessageComponentTestCase {
    public function setUp() {
        parent::setUp();
        $this->_conn->httpHeadersReceived = true;
    }

    public function getConnectionClassString() {
        return '\Ratchet\ConnectionInterface';
    }

    public function getDecoratorClassString() {
        return '\Ratchet\Http\HttpServer';
    }

    public function getComponentClassString() {
        return '\Ratchet\Http\HttpServerInterface';
    }

    public function testOpen() {
        $headers = "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n";

        $this->_conn->httpHeadersReceived = false;
        $this->_app->expects($this->once())->method('onOpen')->with($this->isExpectedConnection());
        $this->_serv->onMessage($this->_conn, $headers);
    }
}