<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

require 'vendor/autoload.php';
require __DIR__ .'/config/connect.php'; 
require __DIR__ .'/config/get_jwt.php';
require __DIR__ .'/config/decode_jwt.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$jwt = get_jwt_from_header();

try {
    $decoded = decode_jwt($jwt);
    $user_id = $decoded->data->id;

    $stmt = $pdo->prepare("
        SELECT f.user_id, f.friend_id, u.full_name
        FROM friends AS f
        JOIN users AS u ON u.id = f.friend_id
        WHERE f.user_id = :uid;
        ");
    $stmt->execute(['uid' => $user_id]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "true",
        "friends" => $friends
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => "false", "error" => "Token không hợp lệ: " . $e->getMessage()]);
}
