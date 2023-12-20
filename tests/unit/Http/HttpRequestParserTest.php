<?php

namespace Ratchet\Http;

use OverflowException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;

/**
 * @covers Ratchet\Http\HttpRequestParser
 */
class HttpRequestParserTest extends TestCase
{
    protected HttpRequestParser $parser;

    public function setUp(): void
    {
        $this->parser = new HttpRequestParser;
    }

    public static function headersProvider(): array
    {
        return [
            [false, "GET / HTTP/1.1\r\nHost: socketo.me\r\n"], [true,  "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n"], [true, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n1"], [true, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie✖"], [true,  "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie✖\r\n\r\n"], [true, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie\r\n"],
        ];
    }

    /**
     * @dataProvider headersProvider
     */
    public function testIsEom($expected, string $message): void
    {
        $this->assertEquals($expected, $this->parser->isEom($message));
    }

    public function testBufferOverflowResponse(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $this->parser->maxSize = 20;

        $this->assertNull($this->parser->onMessage($connection, "GET / HTTP/1.1\r\n"));

        $this->expectException(OverflowException::class);

        $this->parser->onMessage($connection, 'Header-Is: Too Big');
    }

    public function testReturnTypeIsRequest(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $return = $this->parser->onMessage($connection, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n");

        $this->assertInstanceOf(RequestInterface::class, $return);
    }
}
