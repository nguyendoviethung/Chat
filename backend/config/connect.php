<?php
$host = 'localhost';
$dbname = 'ChatApp';
$user = 'postgres'; 
$pass = '2107';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Bạn có thể bật lỗi chi tiết để debug trong dev:
    // $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
} catch (PDOException $e) {
    // Nếu không kết nối được DB thì trả về lỗi json luôn
    http_response_code(500);
    echo json_encode(["error" => "Không thể kết nối database: " . $e->getMessage()]);
    exit();
}
