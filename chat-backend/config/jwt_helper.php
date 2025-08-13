<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;

function create_jwt($userData) {
    $config = require __DIR__ . '/jwt_config.php';

    $payload = [
        "iss" => $config['issuer'],
        "aud" => $config['audience'],
        "iat" => time(),
        "exp" => time() + $config['expire_time'],
        "data" => $userData
    ];

    return JWT::encode($payload, $config['key'], 'HS256');
}
