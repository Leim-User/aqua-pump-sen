<?php

$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "aqua_pump";

$connect = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

if (!$connect) {
    dir("Something went wrong!");
}

mysqli_set_charset($connect, "utf8mb4");

?>