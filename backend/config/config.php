<?php

$dbHost = "sql312.infinityfree.com";
$dbUser = " if0_43057538 ";
$dbPass = "Lema1309";
$dbName = "if0_43057538_aqua_pump";

$connect = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

if (!$connect) {
    die("Something went wrong!");
}

mysqli_set_charset($connect, "utf8mb4");
