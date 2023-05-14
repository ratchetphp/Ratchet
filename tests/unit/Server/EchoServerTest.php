<?php
namespace Ratchet\Server;
use Ratchet\RatchetTestCase;
use Ratchet\Server\EchoServer;

/**
 * @covers \Ratchet\Server\EchoServer
 */
class EchoServerTest extends RatchetTestCase {
    protected $_conn;
    protected $_comp;

    /**
     * @before
     */
    public function before() {
        $this->_conn = $this->_getMock('\Ratchet\ConnectionInterface');
        $this->_comp = new EchoServer;
    }

    public function testMessageEchod() {
        $message = 'Tillsonburg, my back still aches when I hear that word.';
        $this->_conn->expects($this->once())->method('send')->with($message);
        $this->_comp->onMessage($this->_conn, $message);
    }

    public function testErrorClosesConnection() {
        ob_start();
        $this->_conn->expects($this->once())->method('close');
        $this->_comp->onError($this->_conn, new \Exception);
        ob_end_clean();
    }
}
