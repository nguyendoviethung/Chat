<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;    

class ChatServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        // SplObjectStorage lưu danh sách client kết nối
        $this->clients = new \SplObjectStorage;
        echo "✅ WebSocket Server started...\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "🔌 New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "📩 Received: $msg\n";

        $data = json_decode($msg, true);
        if (!$data) {
            echo "⚠️ Invalid message format\n";
            return;
        }

        // Broadcast lại cho tất cả client
        foreach ($this->clients as $client) {
            $client->send(json_encode([
                "user" => $data["user"] ?? "Guest",
                "text" => $data["text"] ?? "",
                "time" => date("H:i:s")
            ]));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "❌ Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "⚠️ Error: {$e->getMessage()}\n";
        $conn->close();
    }
}

$port = 9000; // Cổng mặc định cho WebSocket
$server = \Ratchet\Server\IoServer::factory(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            new ChatServer()
        )
    ),
    $port
);

echo "Server running on ws://localhost:$port\n";
$server->run();

?>