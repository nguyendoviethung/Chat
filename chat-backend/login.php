<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

require __DIR__ . '/connect.php'; 
require __DIR__ . '/config/jwt_helper.php'; 

// Nếu là preflight request, trả về ngay
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Lấy dữ liệu JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["error" => "Thiếu username hoặc password"]);
    exit();
}

$username = $data['username'];
$password = $data['password'];

try {
    
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode(["error" => "Sai tên đăng nhập hoặc mật khẩu"]);
        exit();
    }

    if (!$password) {
        http_response_code(401);
        echo json_encode(["error" => "Sai tên đăng nhập hoặc mật khẩu"]);
        exit();
    }

    $jwt = create_jwt($user['id'] , $user['username']);

    echo json_encode([
        "token" => $jwt,
        "message" => "Đăng nhập thành công",
        "status" => "true"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Lỗi server: " . $e->getMessage()]);
}
