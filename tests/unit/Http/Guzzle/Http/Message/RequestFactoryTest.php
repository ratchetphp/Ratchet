<?php
namespace Ratchet\Http\Guzzle\Http\Message;
use Ratchet\Http\Guzzle\Http\Message\RequestFactory;

/**
 * @covers Ratchet\Http\Guzzle\Http\Message\RequestFactory
 */
class RequestFactoryTest extends \PHPUnit_Framework_TestCase {
    protected $factory;

    public function setUp() {
        $this->factory = RequestFactory::getInstance();
    }

    public function testMessageProvider() {
        return array(
            'status' => 'GET / HTTP/1.1'
          , 'headers' => array(
                'Upgrade'    => 'WebSocket'
              , 'Connection' => 'Upgrade'
              , 'Host'       => 'localhost:8000'
              , 'Sec-WebSocket-Key1' => '> b3lU Z0 fh f 3+83394 6  (zG4'
              , 'Sec-WebSocket-Key2' => ',3Z0X0677 dV-d [159 Z*4'
            )
          , 'body' => "123456\r\n\r\n"
        );
    }

    public function combineMessage($status, array $headers, $body = '') {
        $message = $status . "\r\n";

        foreach ($headers as $key => $val) {
            $message .= "{$key}: {$val}\r\n";
        }

        $message .= "\r\n{$body}";

        return $message;
    }

    public function testExpectedDataFromGuzzleHeaders() {
        $parts   = $this->testMessageProvider();
        $message = $this->combineMessage($parts['status'], $parts['headers'], $parts['body']);
        $object  = $this->factory->fromMessage($message);

        foreach ($parts['headers'] as $key => $val) {
            $this->assertEquals($val, $object->getHeader($key, true));
        }
    }

    public function testExpectedDataFromNonGuzzleHeaders() {
        $parts   = $this->testMessageProvider();
        $message = $this->combineMessage($parts['status'], $parts['headers'], $parts['body']);
        $object  = $this->factory->fromMessage($message);

        $this->assertNull($object->getHeader('Nope', true));
        $this->assertNull($object->getHeader('Nope'));
    }

    public function testExpectedDataFromNonGuzzleBody() {
        $parts   = $this->testMessageProvider();
        $message = $this->combineMessage($parts['status'], $parts['headers'], $parts['body']);
        $object  = $this->factory->fromMessage($message);

        $this->assertEquals($parts['body'], (string)$object->getBody());
    }
}
