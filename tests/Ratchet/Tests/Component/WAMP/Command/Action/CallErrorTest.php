<?php
namespace Ratchet\Tests\Component\WAMP\Command\Action;
use Ratchet\Component\WAMP\Command\Action\CallError;
use Ratchet\Tests\Mock\Connection;

/**
 * @covers Ratchet\Component\WAMP\Command\Action\CallError
 */
class CallErrorTest extends \PHPUnit_Framework_TestCase {
    public function testCallError() {
        $error = new CallError(new Connection);

        $callId = uniqid();
        $uri    = 'http://example.com/end/point';

        $error->setError($callId, $uri);
        $resultString = $error->getMessage();

        $this->assertEquals(array(4, $callId, $uri, ''), json_decode($resultString, true));
    }

    public function testDetailedCallError() {
        $error = new CallError(new Connection);

        $callId = uniqid();
        $uri    = 'http://example.com/end/point';
        $desc   = 'beep boop beep';
        $detail = 'Error: Too much awesome';

        $error->setError($callId, $uri, $desc, $detail);
        $resultString = $error->getMessage();

        $this->assertEquals(array(4, $callId, $uri, $desc, $detail), json_decode($resultString, true));
    }

    public function testGetId() {
        $id = uniqid();

        $error = new CallError(new Connection);
        $error->setError($id, 'http://example.com');

        $this->assertEquals($id, $error->getId());
    }

    public function testGetUri() {
        $uri = 'http://example.com/end/point';

        $error = new CallError(new Connection);
        $error->setError(uniqid(), $uri);

        $this->assertEquals($uri, $error->getUri());
    }

    public function testGetDescription() {
        $desc = uniqid();

        $error = new CallError(new Connection);
        $error->setError(uniqid(), 'curie', $desc);

        $this->assertEquals($desc, $error->getDescription());
    }

    public function testGetDetails() {
        $detail = uniqid();

        $error = new CallError(new Connection);
        $this->assertNull($error->getDetails());
        $error->setError(uniqid(), 'http://socketo.me', 'desc', $detail);

        $this->assertEquals($detail, $error->getDetails());
    }
}