<?php

require __DIR__ . '/../../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;

class DemoChat implements MessageComponentInterface {
    protected array $clients = [];

    public function onOpen(ConnectionInterface $conn) {
        $this->clients[$conn->resourceId] = $conn;
        echo "[" . date('H:i:s') . "] New connection: ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "[" . date('H:i:s') . "] Message received from {$from->resourceId}: $msg\n";

        foreach ($this->clients as $client) {
            try {
                $client->send("Client {$from->resourceId} says: $msg");
            } catch (\Exception $e) {
                echo "[" . date('H:i:s') . "] Error while sending message to client {$client->resourceId}: {$e->getMessage()}\n";
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        unset($this->clients[$conn->resourceId]);
        echo "[" . date('H:i:s') . "] Connection closed: ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "[" . date('H:i:s') . "] Error: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Running WebSocket server at ws://127.0.0.1:8080
$server = IoServer::factory(
    new HttpServer(
        new WsServer(new DemoChat())
    ),
    8080
);

echo "[" . date('H:i:s') . "] WebSocket server started at ws://127.0.0.1:8080\n";
$server->run();
