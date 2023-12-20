<?php

namespace Ratchet\Session\Serialize;

use PHPUnit\Framework\TestCase;

/**
 * @covers Ratchet\Session\Serialize\PhpHandler
 */
class PhpHandlerTest extends TestCase
{
    protected PhpHandler $handler;

    public function setUp(): void
    {
        $this->handler = new PhpHandler;
    }

    public static function serializedProvider(): array
    {
        return [
            [
                '_sf2_attributes|a:2:{s:5:"hello";s:5:"world";s:4:"last";i:1332872102;}_sf2_flashes|a:0:{}', [
                    '_sf2_attributes' => [
                        'hello' => 'world', 'last' => 1332872102,
                    ], '_sf2_flashes' => [],
                ],
            ],
        ];
    }

    /**
     * @dataProvider serializedProvider
     */
    public function testUnserialize($in, $expected)
    {
        $this->assertEquals($expected, $this->handler->unserialize($in));
    }

    /**
     * @dataProvider serializedProvider
     */
    public function testSerialize($serialized, $original)
    {
        $this->assertEquals($serialized, $this->handler->serialize($original));
    }
}
