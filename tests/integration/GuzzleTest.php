<?php
use Guzzle\Http\Message\Request;

class GuzzleTest extends \PHPUnit_Framework_TestCase {
    protected $_request;

    protected $_headers = array(
        'Upgrade' => 'websocket'
      , 'Connection' => 'Upgrade'
      , 'Host' => 'localhost:8080'
      , 'Origin' => 'chrome://newtab'
      , 'Sec-WebSocket-Protocol' => 'one, two, three'
      , 'Sec-WebSocket-Key' => '9bnXNp3ae6FbFFRtPdiPXA=='
      , 'Sec-WebSocket-Version' => '13'
    );

    public function setUp() {
        $this->_request = new Request('GET', 'http://localhost', $this->_headers);
    }

    public function testGetHeaderString() {
        $this->assertEquals('Upgrade', (string)$this->_request->getHeader('connection'));
        $this->assertEquals('9bnXNp3ae6FbFFRtPdiPXA==', (string)$this->_request->getHeader('Sec-Websocket-Key'));
    }

    public function testGetHeaderInteger() {
        $this->assertSame('13', (string)$this->_request->getHeader('Sec-Websocket-Version'));
        $this->assertSame(13, (int)(string)$this->_request->getHeader('Sec-WebSocket-Version'));
    }

    public function testGetHeaderObject() {
        $this->assertInstanceOf('Guzzle\Http\Message\Header', $this->_request->getHeader('Origin'));
        $this->assertNull($this->_request->getHeader('Non-existant-header'));
    }

    public function testHeaderObjectNormalizeValues() {
        $expected  = 1 + substr_count($this->_headers['Sec-WebSocket-Protocol'], ',');
        $protocols = $this->_request->getHeader('Sec-WebSocket-Protocol')->normalize();
        $count     = 0;

        foreach ($protocols as $protocol) {
            $count++;
        }

        $this->assertEquals($expected, $count);
        $this->assertEquals($expected, count($protocols));
    }

    public function testRequestFactoryCreateSignature() {
        $ref = new \ReflectionMethod('Guzzle\Http\Message\RequestFactory', 'create');
        $this->assertEquals(2, $ref->getNumberOfRequiredParameters());
    }
}