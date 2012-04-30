<?php
namespace Ratchet\Tests\Resource\Command\Action;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Tests\Mock\Connection;

/**
 * @covers Ratchet\Resource\Command\Action\SendMessage
 */
class SendMessageTest extends \PHPUnit_Framework_TestCase {
    public function testFluentInterface() {
        $cmd = new SendMessage(new Connection);
        $this->assertInstanceOf('\\Ratchet\\Resource\\Command\\Action\\SendMessage', $cmd->setMessage('Hello World!'));
    }

    public function testGetMessageMatchesSet() {
        $msg = 'The quick brown fox jumps over the lazy dog.';
        $cmd = new SendMessage(new Connection);
        $cmd->setMessage($msg);

        $this->assertEquals($msg, $cmd->getMessage());
    }
}