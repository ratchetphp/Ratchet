<?php

    require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';

    $port = $argc > 1 ? $argv[1] : 8000;
    $impl = sprintf('React\EventLoop\%sLoop', $argc > 2 ? $argv[2] : 'StreamSelect');

    $loop = new $impl;
    $sock = new React\Socket\Server('0.0.0.0:' . $port, $loop);
    $app  = new Ratchet\Http\HttpServer(new Ratchet\WebSocket\WsServer(new Ratchet\Server\EchoServer));

    $server = new Ratchet\Server\IoServer($app, $sock, $loop);
    $server->run();
