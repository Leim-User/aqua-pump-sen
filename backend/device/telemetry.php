<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../config/config.php";

// =============================================
// 1. REQUEST VALIDATION
// =============================================

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

// =============================================
// 2. EXTRACT TELEMETRY DATA
// =============================================

$device_id = $data["device_id"] ?? "";
$mode = $data["mode"] ?? "";

$bucket = $data["bucket_detected"] ?? null;
$distance = $data["distance_cm"] ?? null;

$pump = $data["pump_status"] ?? "";
$fault = $data["fault"] ?? "NONE";

$pump_id = 1;

// =============================================
// 3. VALIDATE TELEMETRY
// =============================================

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

// =============================================
// 4. NORMALIZE VALUES
// =============================================

$distance = ($distance === null)
    ? null
    : (float) $distance;

$pump_status = ($pump === "ON")
    ? "RUNNING"
    : "STOPPED";

$fault = trim($fault);

if (!defined("AQUA_DEVICE_USER_ID")) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Device owner not configured"
    ]);

    exit;
}

$history_user_id = (int) AQUA_DEVICE_USER_ID;

// =============================================
// 5. DATABASE TRANSACTION
// =============================================

try {

    mysqli_begin_transaction($connect);

    // =========================================
    // A. LOCK AND GET CURRENT PUMP STATE
    // =========================================

    $sql = "SELECT status, pump_started_at
            FROM pumps
            WHERE pump_id = ?
            FOR UPDATE";

    $stmt = mysqli_prepare($connect, $sql);

    mysqli_stmt_bind_param($stmt, "i", $pump_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $current_pump = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$current_pump) {
        throw new Exception("Pump record not found.");
    }

    $old_status = $current_pump["status"];
    $started_at = $current_pump["pump_started_at"];

    // =========================================
    // B. GET PREVIOUS TELEMETRY
    // =========================================

    $sql = "SELECT fault, bucket_detected, distance_cm
            FROM device_telemetry
            WHERE device_id = ?
            ORDER BY id DESC
            LIMIT 1";

    $stmt = mysqli_prepare($connect, $sql);

    mysqli_stmt_bind_param($stmt, "s", $device_id);
    mysqli_stmt_execute($stmt);

    $previous_result = mysqli_stmt_get_result($stmt);
    $previous_telemetry = mysqli_fetch_assoc($previous_result);

    mysqli_stmt_close($stmt);

    $previous_fault = $previous_telemetry
        ? strtoupper(trim($previous_telemetry["fault"] ?? "NONE"))
        : "NONE";

    $previous_distance_problem = (
        $previous_telemetry &&
        (int) $previous_telemetry["bucket_detected"] === 1 &&
        $previous_telemetry["distance_cm"] === null
    );

    // =========================================
    // C. SAVE NEW TELEMETRY
    // =========================================

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
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($connect, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sisidss",
        $device_id,
        $pump_id,
        $mode,
        $bucket,
        $distance,
        $pump,
        $fault
    );

    mysqli_stmt_execute($stmt);

    $reading_id = mysqli_insert_id($connect);

    mysqli_stmt_close($stmt);

    // =========================================
    // D. ALERT INSERTION FUNCTION
    // =========================================

    $insertAlert = function ($title, $message, $type) use ($connect, $history_user_id) {

        $sql = "INSERT INTO alerts
                (user_id, title, message, alert_type)
                VALUES (?, ?, ?, ?)";

        $stmt = mysqli_prepare($connect, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "isss",
            $history_user_id,
            $title,
            $message,
            $type
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    };

    // =========================================
    // E. HARDWARE FAULT ALERTS
    // =========================================

    if ($fault !== "NONE") {

        // Generate an alert only for a new or changed fault.

        if ($fault !== $previous_fault) {

            $insertAlert(
                "Hardware Fault Detected",
                "The ESP32 reported a hardware fault: " . $fault,
                "CRITICAL"
            );
        }

    } elseif ($previous_fault !== "NONE") {

        // Notify when the previous fault is cleared.

        $insertAlert(
            "Hardware Fault Cleared",
            "The previously reported hardware fault (" .
            $previous_fault .
            ") is no longer being reported by the ESP32.",
            "INFO"
        );
    }

    // =========================================
    // F. ULTRASONIC SENSOR ALERTS
    // =========================================

    $current_distance_problem = (
        $bucket === true &&
        $distance === null
    );

    if ($current_distance_problem && !$previous_distance_problem) {

        $insertAlert(
            "Ultrasonic Sensor Warning",
            "A container has been detected, but no valid ultrasonic distance reading was received.",
            "WARNING"
        );

    } elseif (!$current_distance_problem && $previous_distance_problem) {

        $insertAlert(
            "Ultrasonic Sensor Recovered",
            "The ultrasonic distance reading is available again or the container is no longer detected.",
            "INFO"
        );
    }

    // =========================================
    // G. DETECT PUMP STATE TRANSITION
    // =========================================

    if ($old_status !== $pump_status) {

        // =====================================
        // PUMP STARTED
        // =====================================

        if ($pump_status === "RUNNING") {

            $sql = "UPDATE pumps
                    SET status = 'RUNNING',
                        pump_started_at = NOW()
                    WHERE pump_id = ?";

            $stmt = mysqli_prepare($connect, $sql);

            mysqli_stmt_bind_param($stmt, "i", $pump_id);
            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);

            // Record pump start event.

            $event = "Pump Started";
            $history_pump_id = "Pump " . $pump_id;
            $history_status = "Completed";

            $sql = "INSERT INTO history
                    (
                        user_id,
                        pump_id,
                        event,
                        duration,
                        water_used,
                        status
                    )
                    VALUES (?, ?, ?, NULL, NULL, ?)";

            $stmt = mysqli_prepare($connect, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "isss",
                $history_user_id,
                $history_pump_id,
                $event,
                $history_status
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);
        }

        // =====================================
        // PUMP STOPPED
        // =====================================
        elseif ($pump_status === "STOPPED") {

            // Calculate actual runtime in seconds.

            $duration = null;

            if ($started_at !== null) {

                $sql = "SELECT TIMESTAMPDIFF(
                            SECOND,
                            ?,
                            NOW()
                        ) AS runtime";

                $stmt = mysqli_prepare($connect, $sql);

                mysqli_stmt_bind_param(
                    $stmt,
                    "s",
                    $started_at
                );

                mysqli_stmt_execute($stmt);

                $duration_result = mysqli_stmt_get_result($stmt);

                $duration_data = mysqli_fetch_assoc($duration_result);

                $duration = max(
                    0,
                    (int) $duration_data["runtime"]
                );

                mysqli_stmt_close($stmt);
            }

            // Update pump record.

            $sql = "UPDATE pumps
                    SET status = 'STOPPED',
                        pump_started_at = NULL
                    WHERE pump_id = ?";

            $stmt = mysqli_prepare($connect, $sql);

            mysqli_stmt_bind_param($stmt, "i", $pump_id);
            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);

            // Record pump stop event.

            $event = "Pump Stopped";
            $history_pump_id = "Pump " . $pump_id;
            $history_status = "Completed";

            $sql = "INSERT INTO history
                    (
                        user_id,
                        pump_id,
                        event,
                        duration,
                        water_used,
                        status
                    )
                    VALUES (?, ?, ?, ?, NULL, ?)";

            $stmt = mysqli_prepare($connect, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "issis",
                $history_user_id,
                $history_pump_id,
                $event,
                $duration,
                $history_status
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);
        }
    }

    // =========================================
    // H. COMMIT TRANSACTION
    // =========================================

    mysqli_commit($connect);

    // =========================================
    // 6. SUCCESS RESPONSE
    // =========================================

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "message" => "Telemetry received and synchronized",
        "reading_id" => $reading_id,
        "pump_status" => $pump_status,
        "fault" => $fault
    ]);

} catch (Throwable $e) {

    mysqli_rollback($connect);

    error_log(
        "Aqua Pump Telemetry Error: " . $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Telemetry processing failed"
    ]);
}

mysqli_close($connect);

?>