<?php

namespace Ratchet\Http;

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

    public function testReturnTypeIsRequest(): void
    {
        $connection = $this->getMockBuilder(ConnectionInterface::class)->getMock();

        $this->assertInstanceOf(
            RequestInterface::class,
            $this->parser->onMessage($connection, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n"),
        );
    }
}
