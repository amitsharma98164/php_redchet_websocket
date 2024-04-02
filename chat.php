<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class MyWebSocketServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userCount = 0;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Increment user count
        $this->userCount++;

        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection established. ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Determine sender's username
        $username = ($from->resourceId === 1) ? "You" : "User ({$from->resourceId})";
    
        // Prepare the message object including username
        $messageObject = json_encode(["username" => $username, "message" => $msg]);
    
        // Broadcast the message to all connected clients
        foreach ($this->clients as $client) {
            $client->send($messageObject);
        }
    }
         

    public function onClose(ConnectionInterface $conn) {
        // Decrement user count
        $this->userCount--;

        // Remove the connection from the list of clients
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        // Close the connection
        $conn->close();
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
?>
