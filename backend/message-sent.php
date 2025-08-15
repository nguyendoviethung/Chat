<?php 
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");
require __DIR__ . '/config/connect.php'; 
require __DIR__ . '/config/get_jwt.php';
require __DIR__ . '/config/decode_jwt.php';


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$friend_id = isset($_GET['friend_id']) ? (int)$_GET['friend_id'] : 0;

if ($friend_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid user_id or friend_id"]);
    exit;
}

$jwt = get_jwt_from_header();
$decode = decode_jwt($jwt);
$user_id = $decode -> data -> id;
  try {
    $stmt = $pdo->prepare("
        SELECT content, sender_id, receiver_id, sent_at
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
 }catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database query failed", "details" => $e->getMessage()]);
 }
