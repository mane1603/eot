<?php
session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


use Steampixel\Route;
use Firebase\JWT\JWT;

$bitrixCodes = json_decode(file_get_contents("codes.json"), 1);

require_once('./vendor/autoload.php');
require_once './src/Steampixel/Route.php';
// require_once './Classes/DBClass.php';
require_once './Controllers/Auth.php';
require_once './Controllers/Bitrix.php';
require_once './Controllers/User.php';
$globalUri = "";
$_SESSION["currency"] =  (isset($_SESSION["currency"])) ? $_SESSION["currency"] : "AMD";

$currency = $_SESSION["currency"];
Route::add('/', function(){
   $auth = new Auth("techforest.cfd", "bGS6lzFqvvSQASdLbOxatm7/Vk7mLQyzqaS34Q4oR1ew=");
   $auth->validate();
}, 'post');

Route::add('/register', function(){
    global $bitrixCodes;
    $credentials = json_decode(file_get_contents("php://input"), 1);
    $login = new User("techforest.cfd", "bGS6lzFqvvSQASdLbOxatm7/Vk7mLQyzqaS34Q4oR1ew=", "https://api.eot.global/rest/1/786wf8ydq2yundnt/");
    $login->register($credentials["username"], $credentials["password"]);
}, 'post');

Route::add('/login', function(){
    global $bitrixCodes;
    $credentials = json_decode(file_get_contents("php://input"), 1);
    $login = new User("techforest.cfd", "bGS6lzFqvvSQASdLbOxatm7/Vk7mLQyzqaS34Q4oR1ew=", "https://api.eot.global/rest/1/786wf8ydq2yundnt/");
    $login->login($credentials["username"], $credentials["password"]);
    // $bitrix = new Bitrix("https://api.eot.global/rest/1/786wf8ydq2yundnt/");
}, 'post');

Route::pathNotFound(function($path) {
  header('HTTP/1.0 404 Not Found');
  include("404.php");
});

// Add a 405 method not allowed route
Route::methodNotAllowed(function($path, $method) {
  // Do not forget to send a status header back to the client
  // The router will not send any headers by default
  // So you will have the full flexibility to handle this case
  header('HTTP/1.0 405 Method Not Allowed');
  echo 'Error 405 :-(<br>';
  echo 'The request method "'.$method.'" is not allowed on this path!';
});
Route::run('/');