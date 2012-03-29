<?php
namespace Ratchet\Tests\Component\Session;
use Ratchet\Component\Session\SessionComponent;
use Ratchet\Tests\Mock\NullMessageComponent;
use Ratchet\Tests\Mock\MemorySessionHandler;

/**
 * @covers Ratchet\Component\Session\SessionComponent
 */
class SessionComponentTest extends \PHPUnit_Framework_TestCase {
    protected $_app;

    public function setUp() {
//        $this->app = new SessionComponent
    }

    /**
     * @return bool
     */
    public function checkSymfonyPresent() {
        return class_exists('Symfony\\Component\\HttpFoundation\\Session\\Session');
    }

    public function classCaseProvider() {
        return array(
            array('php', 'Php')
          , array('php_binary', 'PhpBinary')
        );
    }

    /**
     * @dataProvider classCaseProvider
     */
    public function testToClassCase($in, $out) {
        if (!interface_exists('SessionHandlerInterface')) {
            return $this->markTestSkipped('SessionHandlerInterface not defined. Requires PHP 5.4 or Symfony HttpFoundation');
        }

        $ref = new \ReflectionClass('\\Ratchet\\Component\\Session\\SessionComponent');
        $method = $ref->getMethod('toClassCase');
        $method->setAccessible(true);

        $component = new SessionComponent(new NullMessageComponent, new MemorySessionHandler);
        $this->assertEquals($out, $method->invokeArgs($component, array($in)));
    }

/* Put this in test methods that require Symfony
        if (false === $this->checkSymfonyPresent()) {
            return $this->markTestSkipped('Symfony HttpFoundation not loaded');
        }
*/
}