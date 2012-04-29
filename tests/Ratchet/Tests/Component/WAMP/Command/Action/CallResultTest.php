<?php
namespace Ratchet\Tests\Component\WAMP\Command\Action;
use Ratchet\Component\WAMP\Command\Action\CallResult;
use Ratchet\Tests\Mock\Connection;

/**
 * @covers Ratchet\Component\WAMP\Command\Action\CallResult
 */
class CallResultTest extends \PHPUnit_Framework_TestCase {
    public function testGetMessage() {
        $result = new CallResult(new Connection);

        $callId = uniqid();
        $data   = array('hello' => 'world', 'herp' => 'derp');

        $result->setResult($callId, $data);
        $resultString = $result->getMessage();

        $this->assertEquals(array(3, $callId, $data), json_decode($resultString, true));
    }

    public function testGetId() {
        $id = uniqid();

        $result = new CallResult(new Connection);
        $result->setResult($id, array());

        $this->assertEquals($id, $result->getId());
    }

    public function testGetData() {
        $data = array(
            'hello'     => 'world'
          , 'recursive' => array(
                'the'   => 'quick'
              , 'brown' => 'fox'
            )
          , 'jumps'
        );

        $result = new CallResult(new Connection);
        $result->setResult(uniqid(), $data);

        $this->assertEquals($data, $result->getData());
    }
}