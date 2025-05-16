<?php
namespace Ratchet\Session\Serialize;
use PHPUnit\Framework\TestCase;
use Ratchet\Session\Serialize\PhpBinaryHandler;

/**
 * @covers Ratchet\Session\Serialize\PhpHandler
 */
class PhpBinaryHandlerTest extends TestCase {
    protected $_handler;

    /**
     * @before
     */
    public function setUpHandler() {
        $this->_handler = new PhpBinaryHandler;
    }

    public function serializedProvider() {
        return array(
            array(
                "\x0f" . '_sf2_attributes' . 'a:2:{s:5:"hello";s:5:"world";s:4:"last";i:1332872102;}' . "\x0c" . '_sf2_flashes' . 'a:0:{}'
              , array(
                    '_sf2_attributes' => array(
                        'hello' => 'world'
                      , 'last'  => 1332872102
                    )
                  , '_sf2_flashes' => array()
                )
            )
        );
    }

    /**
     * @dataProvider serializedProvider
     */
    public function testUnserialize($in, $expected) {
        $this->assertEquals($expected, $this->_handler->unserialize($in));
    }
}
