<?php
sesion_start();


require_once "../config/config.php";


// Login check

if (!isset($_SESSION["user_id"])) {
    // http response code: Unauthorized; user not authenticated.
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "User not logged in.";
    ]);

    exit();
}


// Store logged-in users gotten from the session into $user_id

$user_id = $_SESSION["user_id"];


// 

?>