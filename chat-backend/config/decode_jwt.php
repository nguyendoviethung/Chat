<?php
require __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;   
use Firebase\JWT\Key;

function decode_jwt($jwt) {
    $config = require __DIR__ . '/jwt_config.php';
    $key = $config['key'];

    try {
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        return $decoded; 
    } catch (\Firebase\JWT\ExpiredException $e) {
        http_response_code(401);
        echo json_encode(["status" => false, "error" => "Token đã hết hạn"]);
        exit();
    } catch (\Firebase\JWT\SignatureInvalidException $e) {
        http_response_code(401);
        echo json_encode(["status" => false, "error" => "Token không hợp lệ"]);
        exit();
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["status" => false, "error" => "Lỗi khi giải mã token"]);
        exit();
    }
}
