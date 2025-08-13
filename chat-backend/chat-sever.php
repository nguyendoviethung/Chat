<?php
require __DIR__ . '/vendor/autoload.php';
require './connect.php'; 
require __DIR__ . '/config/decode_jwt.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    protected $users; // Lưu user_id => connection
    protected $pdo;

    public function __construct($pdo) {
        $this->clients = new \SplObjectStorage;
        $this->users = [];
        $this->pdo = $pdo;
        echo "WebSocket Server started...\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (!$data || !isset($data['type'])) {
            $from->send(json_encode(["type"=>"error","message"=>"Invalid message format"]));
            return;
        }

        // === Xác thực JWT ===
        if ($data['type'] === 'auth') {
            try {
                $decoded = decode_jwt($data['token']);
                $user_id = $decoded->data->id;
                $this->users[$user_id] = $from; // lưu connection
                $from->send(json_encode(["type"=>"auth_success","user_id"=>$user_id]));
                echo " User $user_id authenticated.\n";
            } catch (\Exception $e) {
                $from->send(json_encode(["type"=>"auth_error","message"=>$e->getMessage()]));
                $from->close();
                echo " Auth failed: {$e->getMessage()}\n";
            }
            return;
        }

        // === Xử lý tin nhắn ===
        if ($data['type'] === 'message') {
            $sender_id = array_search($from, $this->users);
            $receiver_id = $data['receiver_id'] ?? null;
            $text = trim($data['text'] ?? "");

            if (!$sender_id || !$receiver_id || !$text) {
                $from->send(json_encode(["type"=>"error","message"=>"Missing sender/receiver/text"]));
                return;
            }

            // Lưu vào DB
            try {
                $stmt = $this->pdo->prepare("
                    INSERT INTO private_messages (sender_id, receiver_id, content)
                    VALUES (:sender_id, :receiver_id, :content)
                ");
                $stmt->execute([
                    ':sender_id' => $sender_id,
                    ':receiver_id' => $receiver_id,
                    ':content' => $text
                ]);
            } catch (\PDOException $e) {
                $from->send(json_encode(["type"=>"error","message"=>"DB error: ".$e->getMessage()]));
                return;
            }

            $saved_at = date("H:i:s");

            // Gửi tới receiver nếu online
            if (isset($this->users[$receiver_id])) {
                $this->users[$receiver_id]->send(json_encode([
                    "type" => "message",
                    "user" => $sender_id,
                    "text" => $text,
                    "time" => $saved_at
                ]));
            }

            // Gửi phản hồi cho sender
            $from->send(json_encode([
                "type" => "message",
                "user" => $sender_id,
                "text" => $text,
                "time" => $saved_at
            ]));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        // Xóa user khỏi danh sách users nếu tồn tại
        foreach ($this->users as $uid => $connection) {
            if ($connection === $conn) {
                unset($this->users[$uid]);
                echo " User $uid disconnected.\n";
                break;
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo " Error: {$e->getMessage()}\n";
        $conn->close();
    }
}

// === Chạy server ===
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
