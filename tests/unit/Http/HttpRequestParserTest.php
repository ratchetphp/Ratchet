<?php

namespace Ratchet\Http;

/**
 * @covers Ratchet\Http\HttpRequestParser
 */
class HttpRequestParserTest extends \PHPUnit_Framework_TestCase {
    protected $parser;

    #[\Override]
    public function setUp() {
        $this->parser = new HttpRequestParser;
    }

    public function headersProvider() {
        return [
            [false, "GET / HTTP/1.1\r\nHost: socketo.me\r\n"], [true,  "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n"], [true, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n1"], [true, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie✖"], [true,  "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie✖\r\n\r\n"], [true, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie\r\n"],
        ];
    }

    /**
     * @dataProvider headersProvider
     */
    public function testIsEom($expected, $message): void {
        $this->assertEquals($expected, $this->parser->isEom($message));
    }

    public function testBufferOverflowResponse(): void {
        $conn = $this->getMock(\Ratchet\ConnectionInterface::class);

        $this->parser->maxSize = 20;

        $this->assertNull($this->parser->onMessage($conn, "GET / HTTP/1.1\r\n"));

        $this->setExpectedException('OverflowException');

        $this->parser->onMessage($conn, "Header-Is: Too Big");
    }

    public function testReturnTypeIsRequest(): void {
        $conn = $this->getMock(\Ratchet\ConnectionInterface::class);
        $return = $this->parser->onMessage($conn, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n");

        $this->assertInstanceOf(\Psr\Http\Message\RequestInterface::class, $return);
    }
}
