<?php
use Ratchet\ConnectionInterface;

    require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';

class BinaryEcho implements \Ratchet\WebSocket\MessageComponentInterface {
    public function onMessage(ConnectionInterface $from, \Ratchet\RFC6455\Messaging\MessageInterface $msg) {
        $from->send($msg);
    }

    public function onOpen(ConnectionInterface $conn) {
    }

    public function onClose(ConnectionInterface $conn) {
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}

    $port = $argc > 1 ? $argv[1] : 8000;
    $impl = sprintf('React\EventLoop\%sLoop', $argc > 2 ? $argv[2] : 'StreamSelect');

    $loop = new $impl;
    $sock = new React\Socket\Server('0.0.0.0:' . $port, $loop);

    $wsServer = new Ratchet\WebSocket\WsServer(new BinaryEcho);
    // This is enabled to test https://github.com/ratchetphp/Ratchet/issues/430
    // The time is left at 10 minutes so that it will not try to every ping anything
    // This causes the Ratchet server to crash on test 2.7
    $wsServer->enableKeepAlive($loop, 600);

    $app = new Ratchet\Http\HttpServer($wsServer);

    $server = new Ratchet\Server\IoServer($app, $sock, $loop);
    $server->run();
