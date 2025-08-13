<?php
// jwt_config.php
return [
    'key' => 'mySuperSecretKey123!@#', // Khóa bí mật
    'issuer' => 'chat-backend',        // iss
    'audience' => 'chat-frontend',     // aud
    'expire_time' => 3600              // 1 giờ
];
