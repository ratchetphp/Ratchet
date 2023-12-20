<?php

namespace Ratchet\Session\Storage;

use PHPUnit\Framework\TestCase;
use Ratchet\Session\Serialize\PhpHandler;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

// class VirtualSessionStoragePDOTest extends TestCase
// {
//     protected VirtualSessionStorage $virtualSessionStorage;

//     protected string|false $pathToDB;

//     public function setUp(): void
//     {
//         if (! extension_loaded('PDO') || ! extension_loaded('pdo_sqlite')) {
//             $this->markTestSkipped('Session test requires PDO and pdo_sqlite');

//             return;
//         }

//         $schema = <<<'SQL'
// CREATE TABLE `sessions` (
//     `id` VARBINARY(128) NOT NULL PRIMARY KEY,
//     `data` BLOB NOT NULL,
//     `time` INTEGER UNSIGNED NOT NULL,
//     `lifetime` MEDIUMINT NOT NULL
// );
// SQL;
//         $this->pathToDB = tempnam(sys_get_temp_dir(), 'SQ3');
//         $dsn = 'sqlite:'.$this->pathToDB;

//         $pdo = new \PDO($dsn);
//         $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
//         $pdo->exec($schema);
//         $pdo = null;

//         $sessionHandler = new PdoSessionHandler($dsn);
//         $serializer = new PhpHandler();
//         $this->virtualSessionStorage = new VirtualSessionStorage($sessionHandler, 'foobar', $serializer);
//         $this->virtualSessionStorage->registerBag(new FlashBag());
//         $this->virtualSessionStorage->registerBag(new AttributeBag());
//     }

//     public function tearDown(): void
//     {
//         unlink($this->pathToDB);
//     }

//     public function testStartWithDSN(): void
//     {
//         $this->virtualSessionStorage->start();

//         $this->assertTrue($this->virtualSessionStorage->isStarted());
//     }
// }
