<?php
session_start();


if (!isset($_SESSION["user_id"])) {
    die("You're not logged in.");
}

// Connect to DB
require_once "../config/config.php";


// Store user_id in a variable
$user_id = $_SESSION["user_id"];

// Test data
$pump_id = "PUMP-03";
$event = "Pump Stopped";
$duration = 15;
$water_used = 7;
$status = "Completed";


// Insert the data into the table
$sql = "INSERT INTO history (user_id, pump_id, event, duration, water_used, status) VALUES (?, ?, ?, ?, ?, ?)";

// Prepare Statement
$stmt = mysqli_prepare($connect, $sql);

if ($stmt) {
    // Bind parameters
    mysqli_stmt_bind_param($stmt, "issiis", $user_id, $pump_id, $event, $duration, $water_used, $status);
    $execute = mysqli_stmt_execute($stmt);

    if ($execute) {
        echo "Data inserted successfully.";
    }
}
else {
    echo "Failed to insert: " . mysqli_error($stmt);
}


?>