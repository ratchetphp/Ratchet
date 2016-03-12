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
    $sock = new React\Socket\Server($loop);
    $app  = new Ratchet\Http\HttpServer(new Ratchet\WebSocket\WsServer(new BinaryEcho));

    $sock->listen($port, '0.0.0.0');

    $server = new Ratchet\Server\IoServer($app, $sock, $loop);
    $server->run();
