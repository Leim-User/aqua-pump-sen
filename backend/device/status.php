<?php

session_start();

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Authentication required"
    ]);

    exit;
}

require_once __DIR__ . "/../config/config.php";

try {

    // Get current pump status
    $sql = "SELECT pump_id, pump_name, status
            FROM pumps
            WHERE pump_id = 1
            LIMIT 1";

    $result = mysqli_query($connect, $sql);

    $pump = mysqli_fetch_assoc($result);

    // Get latest telemetry
    $sql = "SELECT
                device_id,
                mode,
                bucket_detected,
                distance_cm,
                pump_status,
                fault,
                received_at
            FROM device_telemetry
            WHERE device_id = 'DISPENSER_001'
            ORDER BY id DESC
            LIMIT 1";

    $result = mysqli_query($connect, $sql);

    $telemetry = mysqli_fetch_assoc($result);

    if (!$pump) {
        throw new Exception("Pump not found");
    }

    // Determine device connectivity
    $online = false;

    if ($telemetry) {

        $lastSeen = strtotime($telemetry["received_at"]);

        $online = (time() - $lastSeen) <= 10;
    }

    echo json_encode([
        "success" => true,

        "pump" => [
            "id" => $pump["pump_id"],
            "name" => $pump["pump_name"],
            "status" => $pump["status"]
        ],

        "device" => [
            "online" => $online,
            "device_id" => $telemetry["device_id"] ?? null,
            "mode" => $telemetry["mode"] ?? null
        ],

        "sensors" => [
            "container_detected" =>
                isset($telemetry["bucket_detected"])
                ? (bool) $telemetry["bucket_detected"]
                : null,

            "distance_cm" =>
                $telemetry["distance_cm"] ?? null,

            "fault" =>
                $telemetry["fault"] ?? null
        ],

        "last_update" =>
            $telemetry["received_at"] ?? null
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    error_log($e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "Unable to retrieve system status"
    ]);
}

mysqli_close($connect);

?>