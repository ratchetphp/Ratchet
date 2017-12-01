<?php
namespace Ratchet\Session\Serialize;
use Ratchet\Session\Serialize\PhpSerializeHandler;

/**
 * @covers Ratchet\Session\Serialize\PhpSerializeHandler
 */
class PhpSerializeHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PhpSerializeHandler */
    protected $_handler;

    public function setUp() {
        $this->_handler = new PhpSerializeHandler;
    }

    public function serializedProvider() {
        return array(
            array(
                'a:2:{s:15:"_sf2_attributes";a:2:{s:5:"hello";s:5:"world";s:4:"last";i:1332872102;}s:12:"_sf2_flashes";a:0:{}}',
                array(
                    '_sf2_attributes' => array(
                        'hello' => 'world',
                        'last'  => 1332872102
                    ),
                    '_sf2_flashes' => array()
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

    /**
     * @dataProvider serializedProvider
     */
    public function testSerialize($serialized, $original) {
        $this->assertEquals($serialized, $this->_handler->serialize($original));
    }
}
