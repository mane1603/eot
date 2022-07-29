<?php
require_once('/home/bbdevely/eot/vendor/autoload.php');
use Firebase\JWT\JWT;


class Auth{
    protected $_domain;
    protected $_key;
    
    public function __construct($domain, $key){
        $this->_domain = $domain;
        $this->_key = $key;
    }
    
    public function validate(){
        if (!isset($_SERVER['HTTP_AUTHORIZATION']) || ! preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            header('HTTP/1.0 400 Bad Request');
            echo 'Token not found in request';
            exit;
        }
        
        $jwt = $matches[1];
        if (! $jwt) {
            // No token was able to be extracted from the authorization header
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
        
        // $secretKey  = 'bGS6lzFqvvSQ8ALbOxatm7/Vk7mLQyzqaS34Q4oR1ew=';
        $token = JWT::decode($jwt, $this->_key, ['HS512']);
        $now = new DateTimeImmutable();
        $serverName = $this->_domain;
        
        if ($token->iss !== $serverName ||
            $token->nbf > $now->getTimestamp() ||
            $token->exp < $now->getTimestamp())
        {
            header('HTTP/1.1 401 Unauthorized');
            exit;
        }else{
            header("Content-type: application/json");
            echo json_encode(
                [
                    "message" => "Login successfully",
                    $token
                ]
            );
        }
    }
    
    public function newToken($username){
        $tokenId    = base64_encode(random_bytes(16));
        $issuedAt   = new DateTimeImmutable();
        $expire     = $issuedAt->modify('+6 minutes')->getTimestamp();      // Add 60 seconds
        $serverName = $this->_domain;
        
        // Create the token as an array
        $data = [
            'iat'  => $issuedAt->getTimestamp(),    // Issued at: time when the token was generated
            'jti'  => $tokenId,                     // Json Token Id: an unique identifier for the token
            'iss'  => $serverName,                  // Issuer
            'nbf'  => $issuedAt->getTimestamp(),    // Not before
            'exp'  => $expire,                      // Expire
            'data' => [                             // Data related to the signer user
                'userName' => $username,            // User name
            ]
        ];
    
        $token = JWT::encode(
            $data,      //Data to be encoded in the JWT
            $this->_key, // The signing key
            'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
        );
        // Encode the array to a JWT string.
        return $token;
    }
    
}