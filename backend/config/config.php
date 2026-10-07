<?php

// ****** LOCALHOST ******
// $dbHost = "localhost";
// $dbUser = "root";
// $dbPass = "";
// $dbName = "aqua_pump";

// ProFreeHost connection credentials
$dbHost = "sql303.ezyro.com";
$dbUser = "ezyro_43101506";
$dbPass = "#Lema@1309";
$dbName = "ezyro_43101506_aqua_pump";

// Make mysqli throw exceptions on errors so we can catch them below.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $connect = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    mysqli_set_charset($connect, "utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}
