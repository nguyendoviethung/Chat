<?php
function get_jwt_from_header() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["status" => false, "error" => "Thiếu token"]);
        exit();
    }

    if (!preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
        http_response_code(401);
        echo json_encode(["status" => false, "error" => "Token không hợp lệ"]);
        exit();
    }

    return $matches[1]; // trả về JWT
}
