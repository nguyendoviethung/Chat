<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function verify_jwt($jwt) {
    $config = require __DIR__ . '/jwt_config.php';
    return JWT::decode($jwt, new Key($config['key'], 'HS256'));
}
