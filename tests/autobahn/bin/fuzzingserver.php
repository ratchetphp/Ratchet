<?php

use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use React\Socket\SocketServer;

require dirname(dirname(dirname(__DIR__))).'/vendor/autoload.php';

class BinaryEcho implements MessageComponentInterface
{
    public function onMessage(ConnectionInterface $connection, MessageInterface $message)
    {
        $connection->send($message);
    }

    public function onOpen(ConnectionInterface $connection)
    {
    }

    public function onClose(ConnectionInterface $connection)
    {
    }

    public function onError(ConnectionInterface $connection, Exception $exception)
    {
    }
}

$port = $argc > 1 ? $argv[1] : 8000;
$impl = sprintf('React\EventLoop\%sLoop', $argc > 2 ? $argv[2] : 'StreamSelect');

$loop = new $impl;
$sock = new SocketServer('0.0.0.0:'.$port, [], $loop);

$wsServer = new Ratchet\WebSocket\WsServer(new BinaryEcho);
// This is enabled to test https://github.com/ratchetphp/Ratchet/issues/430
// The time is left at 10 minutes so that it will not try to every ping anything
// This causes the Ratchet server to crash on test 2.7
$wsServer->enableKeepAlive($loop, 600);

$app = new Ratchet\Http\HttpServer($wsServer);

$server = new Ratchet\Server\IoServer($app, $sock, $loop);
$server->run();
