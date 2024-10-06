<?php

namespace Ratchet\Server;

class EchoServerTest extends \PHPUnit_Framework_TestCase {
    protected $_conn;

    protected $_comp;

    #[\Override]
    public function setUp() {
        $this->_conn = $this->getMock(\Ratchet\ConnectionInterface::class);
        $this->_comp = new EchoServer;
    }

    public function testMessageEchod(): void {
        $message = 'Tillsonburg, my back still aches when I hear that word.';
        $this->_conn->expects($this->once())->method('send')->with($message);
        $this->_comp->onMessage($this->_conn, $message);
    }

    public function testErrorClosesConnection(): void {
        ob_start();
        $this->_conn->expects($this->once())->method('close');
        $this->_comp->onError($this->_conn, new \Exception);
        ob_end_clean();
    }
}
