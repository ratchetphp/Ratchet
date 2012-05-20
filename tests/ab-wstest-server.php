<?php
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Tests\AbFuzzyServer;

    require dirname(__DIR__) . '/vendor/autoload.php';

    $server = IoServer::factory(new WsServer(new AbFuzzyServer), 8000);

    $server->run();
