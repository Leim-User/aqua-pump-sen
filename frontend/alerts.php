<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../backend/config/config.php";

$user_id = (int) $_SESSION["user_id"];
$device_id = "DISPENSER_001";

// =============================================
// ALERT COUNTS
// =============================================

$sql_critical = "SELECT COUNT(*) AS total FROM alerts WHERE user_id = ? AND alert_type = 'CRITICAL'";
$stmt_critical = mysqli_prepare($connect, $sql_critical);
mysqli_stmt_bind_param($stmt_critical, "i", $user_id);
mysqli_stmt_execute($stmt_critical);
$data_critical = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_critical));
$total_critical = (int) $data_critical["total"];

$sql_warning = "SELECT COUNT(*) AS total FROM alerts WHERE user_id = ? AND alert_type = 'WARNING'";
$stmt_warning = mysqli_prepare($connect, $sql_warning);
mysqli_stmt_bind_param($stmt_warning, "i", $user_id);
mysqli_stmt_execute($stmt_warning);
$data_warning = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_warning));
$total_warning = (int) $data_warning["total"];

$sql_normal = "SELECT COUNT(*) AS total FROM alerts WHERE user_id = ? AND alert_type IN ('NORMAL', 'INFO')";
$stmt_normal = mysqli_prepare($connect, $sql_normal);
mysqli_stmt_bind_param($stmt_normal, "i", $user_id);
mysqli_stmt_execute($stmt_normal);
$data_normal = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_normal));
$total_normal = (int) $data_normal["total"];

// =============================================
// RECENT ALERTS
// =============================================

$sql_alerts = "SELECT alert_id, title, message, alert_type, created_at
               FROM alerts
               WHERE user_id = ?
               ORDER BY created_at DESC
               LIMIT 20";

$stmt_alerts = mysqli_prepare($connect, $sql_alerts);
mysqli_stmt_bind_param($stmt_alerts, "i", $user_id);
mysqli_stmt_execute($stmt_alerts);
$result_alerts = mysqli_stmt_get_result($stmt_alerts);

// =============================================
// LATEST DEVICE TELEMETRY
// =============================================

$sql_telemetry = "SELECT device_id, bucket_detected, distance_cm,
                         pump_status, fault, received_at
                  FROM device_telemetry
                  WHERE device_id = ?
                  ORDER BY received_at DESC
                  LIMIT 1";

$stmt_telemetry = mysqli_prepare($connect, $sql_telemetry);
mysqli_stmt_bind_param($stmt_telemetry, "s", $device_id);
mysqli_stmt_execute($stmt_telemetry);
$result_telemetry = mysqli_stmt_get_result($stmt_telemetry);

$telemetry = mysqli_fetch_assoc($result_telemetry);

// =============================================
// HARDWARE STATUS
// =============================================

$esp32_status = "OFFLINE";
$container_status = "NO DATA";
$ultrasonic_status = "NO DATA";
$pump_status = "UNKNOWN";
$distance_display = "N/A";
$last_telemetry = "No telemetry received";

$device_online = false;

$system_health = "WARNING";
$health_class = "health-warning";
$health_description = "Waiting for device communication.";

if ($telemetry) {

    $last_telemetry_time = strtotime($telemetry["received_at"]);
    $telemetry_age = time() - $last_telemetry_time;

    // Device is online if telemetry is not older than 15 seconds.
    $device_online = ($telemetry_age >= 0 && $telemetry_age <= 15);

    $last_telemetry = date(
        "d M Y, h:i:s A",
        $last_telemetry_time
    );

    if ($device_online) {

        $esp32_status = "ONLINE";

        // IR CONTAINER SENSOR
        $container_status = ((int) $telemetry["bucket_detected"] === 1)
            ? "DETECTED"
            : "NOT DETECTED";

        // ULTRASONIC SENSOR
        if ($telemetry["distance_cm"] !== null) {

            $distance_display = number_format(
                (float) $telemetry["distance_cm"],
                2
            ) . " cm";

            $ultrasonic_status = "READING";
        }

        // PUMP RELAY
        $pump_status = strtoupper($telemetry["pump_status"]);

        // DEVICE FAULT
        $fault = strtoupper(trim($telemetry["fault"] ?? "NONE"));

        if ($fault === "" || $fault === "NONE") {

            $system_health = "HEALTHY";
            $health_class = "health-good";
            $health_description = "The ESP32 is communicating and no hardware fault has been reported.";

        } else {

            $system_health = "CRITICAL";
            $health_class = "health-critical";
            $health_description = "The device has reported a hardware fault: " . $fault;

        }

    } else {

        $esp32_status = "OFFLINE";

        $system_health = "WARNING";
        $health_class = "health-warning";
        $health_description = "No recent telemetry received. Check the ESP32 connection.";

    }
}

// =============================================
// CLEAN UP
// =============================================

mysqli_stmt_close($stmt_critical);
mysqli_stmt_close($stmt_warning);
mysqli_stmt_close($stmt_normal);
mysqli_stmt_close($stmt_alerts);
mysqli_stmt_close($stmt_telemetry);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerts & System | Aqua Pump</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="assets/aq.png">
</head>

<body>

    <aside class="sidebar">

        <div class="brand">
            <div class="brand-icon">#</div>
            <div>
                <h2>Aqua Pump</h2>
                <span>Smart Water Management System</span>
            </div>
        </div>

        <nav class="navigation">

            <a href="index.php" class="nav-link">
                <span>&gt;</span> Dashboard
            </a>

            <a href="control.php" class="nav-link">
                <span>&gt;</span> Control Panel
            </a>

            <a href="history.php" class="nav-link">
                <span>&gt;</span> History
            </a>

            <a href="alerts.php" class="nav-link active">
                <span>&gt;</span> Alerts &amp; System
            </a>

        </nav>

        <div class="sidebar-status">
            <div class="status-icon">●</div>
            <div>
                <strong>System Status</strong>
                <p><?php echo htmlspecialchars($system_health); ?></p>
            </div>
        </div>

        <div class="last-update">
            <a href="logout.php" class="btn btn-warning">Logout</a>
        </div>

    </aside>

    <main class="main-content">

        <header class="topbar">

            <button class="menu-button" id="menuButton" type="button">☰</button>

            <div class="topbar-right">

                <div class="system-online">
                    <span class="online-dot"></span>
                    <?php echo $device_online ? "Device Online" : "Device Offline"; ?>
                </div>

                <div class="notification">
                    🔔
                    <span class="notification-count">
                        <?php echo htmlspecialchars((string) $total_critical); ?>
                    </span>
                </div>

                <div class="date-time">
                    <strong id="currentDate"><?php echo date("d M Y"); ?></strong>
                    <span id="currentTime"><?php echo date("h:i A"); ?></span>
                </div>

                <div class="user-profile">
                    <div class="avatar">A</div>
                    <div>
                        <strong>Admin</strong>
                        <span>System Administrator</span>
                    </div>
                    <span>⌄</span>
                </div>

            </div>

        </header>

        <section class="page-header">
            <div>
                <h1>Alerts &amp; System</h1>
                <p>Monitor system health, alerts and hardware status</p>
            </div>
        </section>

        <!-- SYSTEM OVERVIEW -->

        <section class="system-overview-grid">

            <!-- SYSTEM HEALTH -->

            <div class="panel">

                <div class="panel-header">
                    <div>
                        <h2>System Health</h2>
                        <span>Current operating condition</span>
                    </div>

                    <strong class="<?php echo htmlspecialchars($health_class); ?>">
                        <?php echo htmlspecialchars($system_health); ?>
                    </strong>
                </div>

                <div class="health-meter">
                    <div class="health-progress"></div>
                </div>

                <p class="health-description">
                    <?php echo htmlspecialchars($health_description); ?>
                </p>

            </div>

            <!-- ALERT SUMMARY -->

            <div class="panel">

                <div class="panel-header">
                    <div>
                        <h2>Alert Summary</h2>
                        <span>Recorded alert counts</span>
                    </div>
                </div>

                <div class="alert-counts">

                    <div>
                        <strong><?php echo $total_critical; ?></strong>
                        <span>Critical</span>
                    </div>

                    <div>
                        <strong><?php echo $total_warning; ?></strong>
                        <span>Warning</span>
                    </div>

                    <div>
                        <strong><?php echo $total_normal; ?></strong>
                        <span>Normal</span>
                    </div>

                </div>

            </div>

        </section>

        <!-- SYSTEM ALERTS -->

        <section class="panel alerts-list-panel">

            <div class="panel-header">
                <div>
                    <h2>System Alerts</h2>
                    <span>Most recent system messages</span>
                </div>
            </div>

            <?php if (mysqli_num_rows($result_alerts) > 0): ?>

                <?php while ($alert = mysqli_fetch_assoc($result_alerts)): ?>

                    <?php
                    $alert_type = strtoupper($alert["alert_type"]);

                    if ($alert_type === "CRITICAL") {
                        $alert_class = "critical";
                        $alert_symbol = "!";
                    } elseif ($alert_type === "WARNING") {
                        $alert_class = "warning";
                        $alert_symbol = "!";
                    } elseif ($alert_type === "INFO") {
                        $alert_class = "normal";
                        $alert_symbol = "i";
                    } else {
                        $alert_class = "normal";
                        $alert_symbol = "✓";
                    }

                    $alert_date = date(
                        "d M Y, h:i A",
                        strtotime($alert["created_at"])
                    );
                    ?>

                    <div class="system-alert <?php echo htmlspecialchars($alert_class); ?>">

                        <div class="alert-symbol">
                            <?php echo htmlspecialchars($alert_symbol); ?>
                        </div>

                        <div class="alert-content">

                            <strong>
                                <?php echo htmlspecialchars($alert["title"]); ?>
                            </strong>

                            <p>
                                <?php echo htmlspecialchars($alert["message"]); ?>
                            </p>

                            <small>
                                <?php echo htmlspecialchars($alert_date); ?>
                            </small>

                        </div>

                        <span class="alert-time">
                            <?php echo htmlspecialchars($alert_type); ?>
                        </span>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="system-alert normal">

                    <div class="alert-symbol">✓</div>

                    <div class="alert-content">
                        <strong>No alerts available</strong>
                        <p>There are currently no recorded system alerts for your account.</p>
                        <small><?php echo date("d M Y, h:i A"); ?></small>
                    </div>

                    <span class="alert-time">NORMAL</span>

                </div>

            <?php endif; ?>

        </section>

        <!-- SENSOR AND CONTROLLER STATUS -->

        <section class="panel sensor-health-panel">

            <div class="panel-header">
                <div>
                    <h2>Sensor &amp; Controller Status</h2>
                    <span>Latest hardware information received from the ESP32</span>
                </div>
            </div>

            <div class="hardware-status-grid">

                <div class="hardware-status">
                    <span>ESP32 Controller</span>
                    <strong><?php echo htmlspecialchars($esp32_status); ?></strong>
                </div>

                <div class="hardware-status">
                    <span>IR Container Sensor</span>
                    <strong><?php echo htmlspecialchars($container_status); ?></strong>
                </div>

                <div class="hardware-status">
                    <span>Ultrasonic Sensor</span>
                    <strong><?php echo htmlspecialchars($ultrasonic_status); ?></strong>
                    <small><?php echo htmlspecialchars($distance_display); ?></small>
                </div>

                <div class="hardware-status">
                    <span>Pump Relay</span>
                    <strong><?php echo htmlspecialchars($pump_status); ?></strong>
                </div>

            </div>

            <p class="health-description">
                Last telemetry:
                <?php echo htmlspecialchars($last_telemetry); ?>
            </p>

        </section>

    </main>

    <script src="js/sidebar.js"></script>

</body>

</html>