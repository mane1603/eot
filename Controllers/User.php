<?php
require_once('/home/bbdevely/eot/vendor/autoload.php');
use Firebase\JWT\JWT;

// require "Bitrix.php";
// require "Auth.php";

class User{
    protected $_domain;
    protected $_key;
    protected $_bitrix;
    protected $_auth;
    
    public function __construct($domain, $key, $webhook){
        $this->_domain = $domain;
        $this->_key = $key;
        $this->_bitrix = new Bitrix($webhook);
        $this->_auth = new Auth($domain, $key);
        $this->_bitrixCodes = json_decode(file_get_contents("/home/bbdevely/eot/codes.json"), 1);
    }
    
    public function register($username, $password){
        $bitrixUser = $this->_bitrix->request("crm.contact.list", [
            "FILTER" => [
                $this->_bitrixCodes["username"] => $username
                ], 
            "SELECT" => ["ID"]
        ]);
        
        if($bitrixUser["total"] == 0){
            if(isset($username) && isset($password)){
                $newData = [
                    $this->_bitrixCodes["username"] => $username,
                    $this->_bitrixCodes["password_hash"] => password_hash($password, PASSWORD_BCRYPT)
                    ];
                $res = $this->_bitrix->request("crm.contact.add", ["FIELDS" => $newData]);
                header("Content-type: application/json;HTTP/1.1 200 OK");
                echo json_encode(
                    [
                        "message" => "Registered successfully",
                        "id" => $res["result"]
                    ]
                );
            }
        }else{
            header("Content-type: application/json;HTTP/1.1 409 Conflict");
            echo json_encode(
                [
                    "message" => "already exists"
                ]
            );
        }
    }
    
    public function login($username, $password){
        $bitrixUser = $this->_bitrix->request("crm.contact.list", [
            "FILTER" => [
                $this->_bitrixCodes["username"] => $username
                ], 
            "SELECT" => ["*", "UF_*"]
        ]);
        
        if(!empty($bitrixUser) && (!isset($bitrixUser["error_description"]) || $bitrixUser["error_description"] == "")){
            $bitrixUser = $bitrixUser["result"][0];
            $hash = $bitrixUser[$this->_bitrixCodes["password_hash"]];
            $verified = password_verify($password, $hash);
            
            if ($verified) {
                $auth = new Auth("techforest.cfd", "bGS6lzFqvvSQASdLbOxatm7/Vk7mLQyzqaS34Q4oR1ew=");
                $token = $auth->newToken($bitrixUser[$this->_bitrixCodes["username"]]);
                header("Content-type: application/json");
                echo json_encode([
                        "token" => $token,
                        "name" => $bitrixUser["NAME"],
                        "surnname" => $bitrixUser["LAST_NAME"]
                    ]
                );
            } else {
                header("Content-type: application/json");
                echo json_encode([
                    "error" => 1,
                    "error_description" => "user not found"
                ], 1);
            }
        }else{
            header("Content-type: application/json");
            echo json_encode([
                "error" => 1,
                "error_description" => "user not found"
            ], 1);
        }
    }
}