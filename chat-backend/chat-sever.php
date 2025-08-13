<?php
require __DIR__ . '/vendor/autoload.php';
require './connect.php'; // file này tạo biến $pdo

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    protected $pdo;

    public function __construct($pdo) {
        $this->clients = new \SplObjectStorage;
        $this->pdo = $pdo;
        echo "WebSocket Server started...\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo " New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Received: $msg\n";

        $data = json_decode($msg, true);
        if (!$data || !isset($data["sender_id"], $data["receiver_id"], $data["text"])) {
            echo "Invalid message format\n";
            return;
        }

        // Lưu tin nhắn vào DB
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO private_messages (sender_id, receiver_id, content)
                VALUES (:sender_id, :receiver_id, :content)
            ");
            $stmt->execute([
                ':sender_id'   => $data["sender_id"],
                ':receiver_id' => $data["receiver_id"],
                ':content'     => $data["text"]
            ]);
            $saved_at = date("H:i:s");
        } catch (PDOException $e) {
            echo " DB insert error: " . $e->getMessage() . "\n";
            return;
        }

        // Broadcast lại cho tất cả client
        foreach ($this->clients as $client) {
            $client->send(json_encode([
                "user" => $data["sender_id"], // Có thể thay bằng username nếu muốn
                "text" => $data["text"],
                "time" => $saved_at
            ]));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Truyền $pdo từ connect.php vào ChatServer
$port = 9000;
$server = \Ratchet\Server\IoServer::factory(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            new ChatServer($pdo)
        )
    ),
    $port
);

echo "Server running on ws://localhost:$port\n";
$server->run();
