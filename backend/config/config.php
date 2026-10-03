<?php

// ── Environment Detection ───────────────────────────────────
// Automatically switches between local (XAMPP) and AwardSpace.
// After creating your AwardSpace database, fill in the values below.

if (strpos($_SERVER['HTTP_HOST'] ?? '', 'atwebpages.com') !== false) {
    $dbHost = getenv("DB_HOST");
    $dbUser = getenv("DB_USER");
    $dbPass = getenv("DB_PASSWORD");
    $dbName = getenv("DB_NAME");
    $dbPort = getenv("DB_PORT") ?: 3306;
} else {
    // ── Local Development (XAMPP) ────────────────────────────
    $dbHost = "localhost";
    $dbUser = "root";
    $dbPass = "";
    $dbName = "aqua_pump";
}

$connect = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

if (!$connect) {
    die("Something went wrong!");
}
