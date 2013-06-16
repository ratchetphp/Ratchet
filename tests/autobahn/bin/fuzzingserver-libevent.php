<?php

    require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';

    $loop = new React\EventLoop\LibEventLoop;
    $sock = new React\Socket\Server($loop);
    $app  = new Ratchet\Http\HttpServer(new Ratchet\WebSocket\WsServer(new Ratchet\Server\EchoServer));

    $port = $argc > 1 ? $argv[1] : 8000;
    $sock->listen($port, '0.0.0.0');

    $server = new Ratchet\Server\IoServer($app, $sock, $loop);
    $server->run();
