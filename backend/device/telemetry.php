<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../config/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "POST method required"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON"
    ]);
    exit;
}

$device_id = $data["device_id"] ?? "";
$mode = $data["mode"] ?? "";
$bucket = $data["bucket_detected"] ?? null;
$distance = $data["distance_cm"] ?? null;
$pump = $data["pump_status"] ?? "";
$fault = $data["fault"] ?? "NONE";

if (
    $device_id !== "DISPENSER_001" ||
    $mode !== "AUTO" ||
    !is_bool($bucket) ||
    !in_array($pump, ["ON", "OFF"], true) ||
    !is_string($fault) ||
    !preg_match('/^[A-Z0-9_]{1,100}$/', $fault) ||
    (
        $distance !== null &&
        (
            !is_numeric($distance) ||
            !is_finite((float) $distance) ||
            (float) $distance < 0 ||
            (float) $distance > 500
        )
    )
) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Invalid telemetry data"
    ]);
    exit;
}

$distance = ($distance === null) ? null : (float) $distance;

$pump_status = ($pump === "ON")
    ? "RUNNING"
    : "STOPPED";

try {

    mysqli_begin_transaction($connect);

    // Save sensor readings
    $sql = "INSERT INTO device_telemetry
        (
            device_id,
            pump_id,
            mode,
            bucket_detected,
            distance_cm,
            pump_status,
            fault
        )
        VALUES (?, 1, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($connect, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssidss",
        $device_id,
        $mode,
        $bucket,
        $distance,
        $pump,
        $fault
    );

    mysqli_stmt_execute($stmt);

    $reading_id = mysqli_insert_id($connect);

    mysqli_stmt_close($stmt);

    // Update the existing pump record
    $sql = "UPDATE pumps
            SET status = ?
            WHERE pump_id = 1";

    $stmt = mysqli_prepare($connect, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $pump_status
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    mysqli_commit($connect);

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "message" => "Telemetry received",
        "reading_id" => $reading_id,
        "pump_status" => $pump_status
    ]);

} catch (Throwable $e) {

    mysqli_rollback($connect);

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Telemetry processing failed"
    ]);
}

mysqli_close($connect);

?>