<?php 
session_start();
/////////////////////////////////////////////////////// TODO
/////////////////////// add delievery 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define("HASH", "SftxEFjRH%2iMFvxLJx\8jjtaGzWgBnI\/O*bDVLlIITW!i@");
$currency = $_SESSION["currency"];
if(!isset($_COOKIE["for_request"])){
    require 'src/jwt/jwt.php';
    $jwt = new JwtHandler();
    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $token = $jwt->_jwt_encode_data(
            $url,
            [
                "time" => date("Y-m-d h:i:sa"),
            "token" => password_hash(HASH, PASSWORD_BCRYPT)
            ]
        );
    setcookie('for_request', $token, time()+(3600*24*60), '/');
}
$data = $_GET;
foreach ($data as $key => $value) {
    $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
// print_r($_COOKIE);
require_once 'Classes/DBClass.php';
require_once "Controllers/Cart.php";
require_once "Controllers/Products.php";
$cart = new CartPage($dbclass, $currency);

if(isset($data["addToCart"]) && $data["addToCart"] !== ""){
    $count = $cart::addItem($slug = $data["slug"], $count = $data["count"], $db = $dbclass);
    header("Content-type: application/json");
    echo json_encode(["count" => $count]);
}
if(isset($data["minusFromCart"]) && $data["minusFromCart"] !== ""){
        $count = $cart::minusFromCart($slug = $data["slug"], $count = $data["count"], $db = $dbclass);
    header("Content-type: application/json");
    echo json_encode(["count" => $count]);
}
if(isset($data["removeItem"]) && $data["removeItem"] !== ""){
        // foreach ($data as $key => $value) {
        //     $data[$key] = $dbclass->escape($value);
    // }
    $count = $cart::removeItem($id = $data["id"], $count = 1, $db = $dbclass);
    header("Content-type: application/json");
    echo json_encode(["count" => $count]);
}
if(isset($data["changeValue"]) && $data["changeValue"] !== "" && $data["newValue"] !== ""){
        // foreach ($data as $key => $value) {
            //     $data[$key] = $dbclass->escape($value);
    // }
    $count = $cart::changeValue($id = $data["id"], $count = $data["newValue"], $props = "", $discount = 0, $db = $dbclass);
    header("Content-type: application/json");
    echo json_encode(["count" => $count]);
}
if(isset($data["coupon_code"]) && $data["addCoupon"] !== ""){
        // foreach ($data as $key => $value) {
        //     $data[$key] = $dbclass->escape($value);
        // }

    $status = $cart::addCoupon($coupon_code = $data["coupon_code"], $db = $dbclass);
    header("Content-type: application/json");
    echo json_encode(["status" => $status]);
}
######################################## LOADINGS ##########################################################
if(isset($data["loadProducts"]) && $data["per_page"] !== ""){
        $products = new Product($dbclass, $currency);
        // $exploded = explode(".", $data["load"]);
    $content = $products->showList(["load" => 1, "per_page" => $data["per_page"], "page" => $data["page"], "company" => (isset($data["company"])) ? $data["company"] : "", "meta" => (isset($data["meta"])) ? $data["meta"] : ""]); 
    header("Content-type: application/json");
    echo json_encode(["content" => $content]);
}
######################################## LOADINGS ##########################################################
######################################## SEARCH   ##########################################################
if(isset($data["search"]) && $data["search"] !== ""){
        header("Location: /search/".$data["search"]);
}
######################################## SEARCH   ##########################################################
######################################## SEARCH   ##########################################################
if(isset($data["cookie"]) && $data["cookie"] == 1){
        setcookie('accepted', "accepted", time()+(3600*24*60), '/');
        $_COOKIE["accepted"] = "accepted";
    if(isset($data["return"])){
            header("Location: " .$data["return"]);
    }else{
            header("Location: /");
    }
}
######################################## SEARCH   ##########################################################
######################################## PRICE TO PRICE SEARCH   ##########################################################
if(isset($data["from"]) || isset($data["to"])){
    header("Location: /search?from=".$data["from"] . "&to=". $data["to"]);
}
######################################## PRICE TO PRICE SEARCH   ##########################################################
######################################## CHANGE CURRENCY   ##########################################################
if(isset($data["currency"])){
    $curs = ["AMD", "USD", "RUB", "EUR"];
    $_SESSION["currency"] = (isset($curs[$data["currency"]])) ? $curs[$data["currency"]] : "AMD";
    header("Location: " . $data["redirect"]);
}
######################################## CHANGE CURRENCY   ##########################################################
if(isset($_POST["paymentMethod"]) 
){
    $py = ["card", "idram", "cash"];
    $error = 0;
    if(strlen($_POST["paymentMethod"]) == 0 || strlen($_POST["state__input"]) == 0 ){
        $error = 1;
    }else if(!in_array($_POST["paymentMethod"], $py)){
        $error = 1;
    }
    if(strlen($_POST["resday"])  == 0 || strlen($_POST["reshour"]) == 0){
        $error = 1;
    }
    if(!isset($_POST["customer__name"]) || !isset($_POST["customer__firstPhone"])){
        $error = 1;
    }
    if(!isset($_POST["customer__secondPhone"]) || !isset($_POST["recipient__address"])){
        $error = 1;
    }
    $data = $_POST;
    foreach ($data as $key => $value) {
        $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    // echo "<pre>";
    // print_r($_POST);
        
    // foreach ($data as $key => $value) {
        //     $data[$key] = $dbclass->escape($value);
        // }

    # ====================================== CART        ======================================
    if($error == 1){
        header("Location: /cart?details=notValid");
    }else{        
        $items = $cart->getItemsbyJWT();
        // print_r($items);
        if(!empty($items)){  
            $continue = true;
            // $price = 0;
            // $quantity = 0;
            // // ------------------------ row_id == product id in translations db
            // foreach ($items as $key => $value) {
            //     if(is_numeric($key)){
            //         $price += $value["sale_price"] * $value["quantity"];
            //     }
            // }
            // $coupon = $items["coupon_code"];
        }else{
            $continue = false;
        }
        if($continue == true){
                # ====================================== ORDER     ======================================
            $order_id = $dbclass->query('INSERT INTO `orders_df` (`id`) VALUES (NULL) ')->lastInsertID();   
            # ====================================== ADDRESSES ======================================
            $delPrice = $dbclass->query("SELECT * FROM `shipping_df` WHERE `id` = ?", $data["state__input"])->fetchArray()["price"];
            $dbclass->query('INSERT INTO `address_df` (`order_id`, `region_city`, `address`, `del_val`) VALUES (?,?,?,?)', 
            [$order_id,  $data["state__input"], $data["recipient__address"],$delPrice]);
            $delAddr = $dbclass->lastInsertID();
            $cart_id = $dbclass->query("SELECT `id` FROM `cart_df` WHERE `user_id` = ? AND `status` = 'new'", $_COOKIE["for_request"])->fetchArray()["id"];
            $dbclass->query('UPDATE `orders_df` SET 
            `user_id` = ? , `address_id` = ? , `payment` = ? , `payment_id` = ?, `coupon` = ? , `currency` = ? , 
            `delievery` = ? , `status` = ?, `currency_now` = ?, `cart_id` = ?, `name` = ?, `phone` = ?, `phone2` = ?,
             `mail` = ?, `resname` = ? , `resphone` = ?, `ordered_for` = ? WHERE id  = ? ',
            [$_COOKIE["for_request"], $delAddr, $data["paymentMethod"], null, "", $currency, 
            "yes", "new", $cart->curVal, $cart_id, $data["customer__name"], $data["customer__firstPhone"], $data["customer__secondPhone"], 
            $data["customer__email"],$data["recipient__name"],$data["recipient__firstPhone"], $data["resday"] . " " . $data["reshour"],$order_id]);
            header("Location: /checkout");
            # ====================================== USER      ======================================
        
            // echo "<pre>";
            // print_r([$user_id, $delAddr, $data["paymentMethod"], $coupon, $price, $currency, "yes", "new", $order_id]);
            # ====================================== PAYMENT METHODS     ==============================
        }else{
            header("Location: /error/");
        }
    }
}
