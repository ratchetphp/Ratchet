<?php
namespace Ratchet\WebSocket;
use Ratchet\WebSocket\VersionManager;
use Ratchet\WebSocket\Version\RFC6455;
use Ratchet\WebSocket\Version\HyBi10;
use Ratchet\WebSocket\Version\Hixie76;
use Guzzle\Http\Message\EntityEnclosingRequest;

/**
 * @covers Ratchet\WebSocket\VersionManager
 */
class VersionManagerTest extends \PHPUnit_Framework_TestCase {
    protected $vm;

    public function setUp() {
        $this->vm = new VersionManager;
    }

    public function testFluentInterface() {
        $rfc = new RFC6455;

        $this->assertSame($this->vm, $this->vm->enableVersion($rfc));
        $this->assertSame($this->vm, $this->vm->disableVersion(13));
    }

    public function testGetVersion() {
        $rfc = new RFC6455;
        $this->vm->enableVersion($rfc);

        $req = new EntityEnclosingRequest('get', '/', array(
            'Host' => 'socketo.me'
          , 'Sec-WebSocket-Version' => 13
        ));

        $this->assertSame($rfc, $this->vm->getVersion($req));
    }

    public function testGetNopeVersionAndDisable() {
        $req = new EntityEnclosingRequest('get', '/', array(
            'Host' => 'socketo.me'
          , 'Sec-WebSocket-Version' => 13
        ));

        $this->setExpectedException('InvalidArgumentException');

        $this->vm->getVersion($req);
    }

    public function testYesIsVersionEnabled() {
        $this->vm->enableVersion(new RFC6455);

        $this->assertTrue($this->vm->isVersionEnabled(new EntityEnclosingRequest('get', '/', array(
            'Host' => 'socketo.me'
          , 'Sec-WebSocket-Version' => 13
        ))));
    }

    public function testNoIsVersionEnabled() {
        $this->assertFalse($this->vm->isVersionEnabled(new EntityEnclosingRequest('get', '/', array(
            'Host' => 'socketo.me'
          , 'Sec-WebSocket-Version' => 9000
        ))));
    }

    public function testGetSupportedVersionString() {
        $v1 = new RFC6455;
        $v2 = new HyBi10;

        $this->vm->enableVersion($v1);
        $this->vm->enableVersion($v2);

        $string = $this->vm->getSupportedVersionString();
        $values = explode(',', $string);

        $this->assertContains($v1->getVersionNumber(), $values);
        $this->assertContains($v2->getVersionNumber(), $values);
    }

    public function testGetSupportedVersionAfterRemoval() {
        $this->vm->enableVersion(new RFC6455);
        $this->vm->enableVersion(new HyBi10);
        $this->vm->enableVersion(new Hixie76);

        $this->vm->disableVersion(0);

        $values = explode(',', $this->vm->getSupportedVersionString());

        $this->assertEquals(2, count($values));
        $this->assertFalse(array_search(0, $values));
    }
}