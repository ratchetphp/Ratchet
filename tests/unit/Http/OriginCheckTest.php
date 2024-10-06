<?php

namespace Ratchet\Http;
use Ratchet\AbstractMessageComponentTestCase;

/**
 * @covers Ratchet\Http\OriginCheck
 */
class OriginCheckTest extends AbstractMessageComponentTestCase {
    protected $_reqStub;

    #[\Override]
    public function setUp() {
        $this->_reqStub = $this->getMock(\Psr\Http\Message\RequestInterface::class);
        $this->_reqStub->expects($this->any())->method('getHeader')->will($this->returnValue(['localhost']));

        parent::setUp();

        $this->_serv->allowedOrigins[] = 'localhost';
    }

    #[\Override]
    protected function doOpen($conn) {
        $this->_serv->onOpen($conn, $this->_reqStub);
    }

    #[\Override]
    public function getConnectionClassString(): string {
        return \Ratchet\ConnectionInterface::class;
    }

    #[\Override]
    public function getDecoratorClassString(): string {
        return \Ratchet\Http\OriginCheck::class;
    }

    #[\Override]
    public function getComponentClassString(): string {
        return \Ratchet\Http\HttpServerInterface::class;
    }

    public function testCloseOnNonMatchingOrigin(): void {
        $this->_serv->allowedOrigins = ['socketo.me'];
        $this->_conn->expects($this->once())->method('close');

        $this->_serv->onOpen($this->_conn, $this->_reqStub);
    }

    public function testOnMessage(): void {
        $this->passthroughMessageTest('Hello World!');
    }
}
