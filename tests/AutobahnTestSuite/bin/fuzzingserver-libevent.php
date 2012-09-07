<?php

    require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';

    $loop = new React\EventLoop\LibEventLoop;
    $sock = new React\Socket\Server($loop);
    $app  = new Ratchet\WebSocket\WsServer(new Ratchet\Tests\AbFuzzyServer);

    $sock->listen(8000, '0.0.0.0');

    $server = new Ratchet\Server\IoServer($app, $sock, $loop);
    $server->run();
