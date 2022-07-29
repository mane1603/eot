<?php
require 'jwt.php';
$jwt = new JwtHandler();

$token = $jwt->_jwt_encode_data(
    'http://localhost/php_jwt/',
    array(
        "time" => time(),
        "token" => password_hash("", PASSWORD_BCRYPT )
        )
);

