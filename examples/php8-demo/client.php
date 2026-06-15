<?php

require __DIR__ . '/vendor/autoload.php';

use Ratchet\Client\Connector;
use React\EventLoop\Loop;

$loop = Loop::get();

$connector = new Connector($loop);

$connector('ws://127.0.0.1:8080')
    ->then(function(Ratchet\Client\WebSocket $conn) use ($loop) {

        echo "[" . date('H:i:s') . "] Connected to WebSocket server\n";

        // Send a test message
        $message = "Hello from PHP 8 client!";
        $conn->send($message);
        echo "[" . date('H:i:s') . "] Message sent: {$message}\n";

        // When a message arrives
        $conn->on('message', function($msg) use ($conn, $loop) {
            echo "[" . date('H:i:s') . "] Received from server: {$msg}\n";

            // Close connection after receiving a message
            $conn->close();
            $loop->stop();
        });

        // When the connection closes
        $conn->on('close', function() {
            echo "[" . date('H:i:s') . "] Connection closed\n";
        });

    }, function(\Exception $e) use ($loop) {
        echo "[" . date('H:i:s') . "] Could not connect: {$e->getMessage()}\n";
        $loop->stop();
    });

$loop->run();
