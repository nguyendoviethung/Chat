<?php 
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Nếu là OPTIONS request thì trả về luôn để tránh CORS block
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require './connect.php'; // file này tạo biến $pdo

$data = json_decode(file_get_contents("php://input"), true);

// Lấy user_id và friend_id từ body
$user_id   = isset($data['user_id']) ? (int)$data['user_id'] : 0;
$friend_id = isset($data['friend_id']) ? (int)$data['friend_id'] : 0;

if ($user_id <= 0 || $friend_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid user_id or friend_id"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, sender_id, receiver_id, content, sent_at
        FROM private_messages
        WHERE 
            (sender_id = :user_id AND receiver_id = :friend_id)
            OR
            (sender_id = :friend_id AND receiver_id = :user_id)
        ORDER BY sent_at ASC
    ");
    $stmt->execute([
        ':user_id'   => $user_id,
        ':friend_id' => $friend_id
    ]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($messages);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database query failed", "details" => $e->getMessage()]);
}
