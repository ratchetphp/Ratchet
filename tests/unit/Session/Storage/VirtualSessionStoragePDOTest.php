<?php
namespace Ratchet\Session\Storage;
use PHPUnit\Framework\TestCase;
use Ratchet\Session\Serialize\PhpHandler;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

class VirtualSessionStoragePDOTest extends TestCase {
    /**
     * @var VirtualSessionStorage
     */
    protected $_virtualSessionStorage;

    protected $_pathToDB;

    /**
     * @before
     */
    public function setUpHandler() {
        if (!extension_loaded('PDO') || !extension_loaded('pdo_sqlite')) {
            return $this->markTestSkipped('Session test requires PDO and pdo_sqlite');
        }

        $schema = <<<SQL
CREATE TABLE `sessions` (
    `sess_id` VARBINARY(128) NOT NULL PRIMARY KEY,
    `sess_data` BLOB NOT NULL,
    `sess_time` INTEGER UNSIGNED NOT NULL,
    `sess_lifetime` MEDIUMINT NOT NULL
);
SQL;
        $this->_pathToDB = tempnam(sys_get_temp_dir(), 'SQ3');;
        $dsn = 'sqlite:' . $this->_pathToDB;

        $pdo = new \PDO($dsn);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec($schema);
        $pdo = null;

        $sessionHandler = new PdoSessionHandler($dsn);
        $serializer = new PhpHandler();
        $this->_virtualSessionStorage = new VirtualSessionStorage($sessionHandler, 'foobar', $serializer);
        $this->_virtualSessionStorage->registerBag(new FlashBag());
        $this->_virtualSessionStorage->registerBag(new AttributeBag());
    }

    /**
     * @after
     */
    public function tearDownHandler() {
        unlink($this->_pathToDB);
    }

    public function testStartWithDSN() {
        $this->_virtualSessionStorage->start();

        $this->assertTrue($this->_virtualSessionStorage->isStarted());
    }
}
