<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class MyWebSocketServer implements MessageComponentInterface {
    // onOpen - Called when a new client has Connected
    public function onOpen(ConnectionInterface $conn) {
        echo "New connection established.\n";
    }

    // onMessage - Called when a message is received by a Connection
    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Received message: $msg\n";
        
    }

    // onClose - Called when a Connection is closed
    public function onClose(ConnectionInterface $conn) {
        echo "Connection closed.\n";
    }

    // onError - Called when an error occurs on a Connection
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error occurred: {$e->getMessage()}\n";
    }
}

// Create WebSocket server
$websocketServer = new WsServer(new MyWebSocketServer());

// Wrap WebSocket server with HTTP server
$httpServer = new HttpServer($websocketServer);

// Initialize IoServer
$server = IoServer::factory(
    $httpServer,
    8080
);

$server->run();
