
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../backend/config/config.php";

$user_id = (int) $_SESSION["user_id"];
$device_id = "DISPENSER_001";
$pump_id = 1;

// =============================================
// HELPER: FORMAT RUNTIME
// =============================================

function formatRuntime($seconds)
{

    $seconds = max(0, (int) $seconds);

    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $remaining = $seconds % 60;

    return sprintf(
        "%02d:%02d:%02d",
        $hours,
        $minutes,
        $remaining
    );
}

// =============================================
// 1. PUMP STATUS
// =============================================

$sql = "SELECT pump_name, status, pump_started_at
        FROM pumps
        WHERE pump_id = ?
        LIMIT 1";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "i", $pump_id);
mysqli_stmt_execute($stmt);

$pump = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

mysqli_stmt_close($stmt);

$pump_status = $pump["status"] ?? "UNKNOWN";
$pump_started_at = $pump["pump_started_at"] ?? null;

$pump_display = ($pump_status === "RUNNING")
    ? "ON"
    : (($pump_status === "STOPPED") ? "OFF" : "UNKNOWN");

$pump_mode = "Automatic Mode";

// =============================================
// 2. LATEST DEVICE TELEMETRY
// =============================================

$sql = "SELECT bucket_detected, distance_cm, pump_status,
               fault, received_at
        FROM device_telemetry
        WHERE device_id = ?
        ORDER BY received_at DESC
        LIMIT 1";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "s", $device_id);
mysqli_stmt_execute($stmt);

$telemetry = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

mysqli_stmt_close($stmt);

// Default values

$device_online = false;
$container_display = "No data";
$distance_display = "N/A";
$last_telemetry = "No telemetry received";
$system_health = "WARNING";

if ($telemetry) {

    $last_telemetry_timestamp = strtotime($telemetry["received_at"]);
    $telemetry_age = time() - $last_telemetry_timestamp;

    $device_online = (
        $telemetry_age >= 0 &&
        $telemetry_age <= 15
    );

    $last_telemetry = date(
        "d M Y, h:i:s A",
        $last_telemetry_timestamp
    );

    if ($device_online) {

        $container_display = (
            (int) $telemetry["bucket_detected"] === 1
        ) ? "DETECTED" : "NOT DETECTED";

        if ($telemetry["distance_cm"] !== null) {

            $distance_display = number_format(
                (float) $telemetry["distance_cm"],
                2
            ) . " cm";
        }

        $fault = strtoupper(trim($telemetry["fault"] ?? "NONE"));

        $system_health = ($fault === "NONE")
            ? "HEALTHY"
            : "CRITICAL";

    }
}

// =============================================
// 3. TODAY'S WATER CONSUMPTION
// =============================================

$sql = "SELECT SUM(water_used) AS total_water
        FROM history
        WHERE user_id = ?
        AND event = 'Pump Stopped'
        AND DATE(created_at) = CURDATE()";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$consumption_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

mysqli_stmt_close($stmt);

$today_water = $consumption_data["total_water"];

$consumption_display = ($today_water !== null)
    ? number_format((float) $today_water, 2) . " L"
    : "Not measured";

// =============================================
// 4. TODAY'S PUMP RUNTIME
// =============================================

$sql = "SELECT COALESCE(SUM(duration), 0) AS total_runtime
        FROM history
        WHERE user_id = ?
        AND event = 'Pump Stopped'
        AND DATE(created_at) = CURDATE()";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$runtime_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

mysqli_stmt_close($stmt);

$today_runtime = (int) $runtime_data["total_runtime"];

// Add current session runtime if pump is running.

if ($pump_status === "RUNNING" && $pump_started_at !== null) {

    $sql = "SELECT TIMESTAMPDIFF(SECOND, ?, NOW()) AS runtime";

    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "s", $pump_started_at);
    mysqli_stmt_execute($stmt);

    $current_runtime_data = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    mysqli_stmt_close($stmt);

    $today_runtime += max(
        0,
        (int) $current_runtime_data["runtime"]
    );
}

$runtime_display = formatRuntime($today_runtime);

// =============================================
// 5. RECENT ACTIVITY
// =============================================

$sql = "SELECT event, pump_id, duration, water_used, status, created_at
        FROM history
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 5";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result_activity = mysqli_stmt_get_result($stmt);

// =============================================
// 6. RECENT ALERTS
// =============================================

$sql = "SELECT title, message, alert_type, created_at
        FROM alerts
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 3";

$stmt_alerts = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt_alerts, "i", $user_id);
mysqli_stmt_execute($stmt_alerts);

$result_alerts = mysqli_stmt_get_result($stmt_alerts);

// Critical alert count

$sql = "SELECT COUNT(*) AS total
        FROM alerts
        WHERE user_id = ?
        AND alert_type = 'CRITICAL'";

$stmt_count = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt_count, "i", $user_id);
mysqli_stmt_execute($stmt_count);

$count_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_count));
$total_critical = (int) $count_data["total"];

// =============================================
// 7. DASHBOARD STATUS
// =============================================

if (!$device_online) {

    $system_health = "OFFLINE";

} elseif ($system_health === "CRITICAL") {

    $system_health = "CRITICAL";

} else {

    $system_health = "HEALTHY";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Aqua Pump</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="assets/aqLogo.png">
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

            <a href="index.php" class="nav-link active">
                <span>&gt;</span> Dashboard
            </a>

            <a href="control.php" class="nav-link">
                <span>&gt;</span> Control Panel
            </a>

            <a href="history.php" class="nav-link">
                <span>&gt;</span> History
            </a>

            <a href="alerts.php" class="nav-link">
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

        <!-- TOPBAR -->

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
                        <?php echo $total_critical; ?>
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

        <!-- PAGE HEADER -->

        <section class="page-header">
            <div>
                <h1>Dashboard</h1>
                <p>Real-time overview of your water pumping system</p>
            </div>
        </section>

        <!-- STATISTICS -->

        <section class="statistics-grid">

            <div class="stat-card pump-card">
                <div class="stat-info">
                    <span class="stat-label">Pump Status</span>
                    <strong id="pumpStatus">
                        <?php echo htmlspecialchars($pump_display); ?>
                    </strong>
                    <small id="pumpMode">
                        <?php echo htmlspecialchars($pump_mode); ?>
                    </small>
                </div>
                <div class="stat-icon blue">#</div>
            </div>

            <div class="stat-card flow-card">
                <div class="stat-info">
                    <span class="stat-label">Flow Rate</span>
                    <strong id="flowRate">N/A</strong>
                    <small>Flow sensor not configured</small>
                </div>
                <div class="stat-icon green">=</div>
            </div>

            <div class="stat-card consumption-card">
                <div class="stat-info">
                    <span class="stat-label">Today's Consumption</span>
                    <strong id="consumption">
                        <?php echo htmlspecialchars($consumption_display); ?>
                    </strong>
                    <small>Based on recorded water measurements</small>
                </div>
                <div class="stat-icon orange">#</div>
            </div>

            <div class="stat-card runtime-card">
                <div class="stat-info">
                    <span class="stat-label">Runtime Today</span>
                    <strong id="runtime">
                        <?php echo htmlspecialchars($runtime_display); ?>
                    </strong>
                    <small>HH:MM:SS</small>
                </div>
                <div class="stat-icon purple">@</div>
            </div>

        </section>

        <!-- MONITORING -->

        <section class="monitoring-grid">

            <!-- CONTAINER MONITORING -->

            <div class="panel water-level-panel">

                <div class="panel-header">
                    <h2>Container Monitoring</h2>
                </div>

                <div class="overview-list">

                    <div class="overview-row">
                        <span>Container Detection</span>
                        <strong>
                            <?php echo htmlspecialchars($container_display); ?>
                        </strong>
                    </div>

                    <div class="overview-row">
                        <span>Ultrasonic Distance</span>
                        <strong>
                            <?php echo htmlspecialchars($distance_display); ?>
                        </strong>
                    </div>

                    <div class="overview-row">
                        <span>Device Connection</span>
                        <strong>
                            <?php echo $device_online ? "ONLINE" : "OFFLINE"; ?>
                        </strong>
                    </div>

                    <div class="overview-row">
                        <span>Last Telemetry</span>
                        <strong>
                            <?php echo htmlspecialchars($last_telemetry); ?>
                        </strong>
                    </div>

                </div>

            </div>

            <!-- AUTOMATIC PUMP STATUS -->

            <div class="panel control-panel">

                <div class="panel-header">
                    <div>
                        <h2>Automatic Pump Operation</h2>
                        <span>Controlled by ESP32 firmware</span>
                    </div>
                </div>

                <div class="control-status">
                    <span class="status-indicator"></span>
                    <div>
                        <small>Current Pump Status</small>
                        <p id="controlMessage">
                            <?php
                            if (!$device_online) {
                                echo "Device connection unavailable";
                            } elseif ($pump_status === "RUNNING") {
                                echo "Pump is currently running";
                            } elseif ($pump_status === "STOPPED") {
                                echo "Pump is currently stopped";
                            } else {
                                echo "Pump status unavailable";
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <p class="health-description">
                    Automatic filling is managed by the ESP32.
                    Use the Control Panel to monitor the device.
                </p>

                <a href="control.php" class="view-history">
                    Open Control Panel →
                </a>

            </div>

            <!-- SYSTEM OVERVIEW -->

            <div class="panel overview-panel">

                <div class="panel-header">
                    <div>
                        <h2>System Overview</h2>
                        <span>Current system information</span>
                    </div>
                </div>

                <div class="overview-list">

                    <div class="overview-row">
                        <span>Controller</span>
                        <strong>ESP32</strong>
                    </div>

                    <div class="overview-row">
                        <span>Operating Mode</span>
                        <strong>AUTO</strong>
                    </div>

                    <div class="overview-row">
                        <span>Connection</span>
                        <strong>
                            <?php echo $device_online ? "Online" : "Offline"; ?>
                        </strong>
                    </div>

                    <div class="overview-row">
                        <span>Pump</span>
                        <strong>
                            <?php echo htmlspecialchars($pump_display); ?>
                        </strong>
                    </div>

                    <div class="overview-row">
                        <span>System Health</span>
                        <strong><?php echo htmlspecialchars($system_health); ?></strong>
                    </div>

                </div>

            </div>

        </section>

        <!-- RECENT ACTIVITY AND ALERTS -->

        <section class="bottom-grid">

            <!-- RECENT ACTIVITY -->

            <div class="panel activity-panel">

                <div class="panel-header">
                    <h2>Recent Activity</h2>
                </div>

                <div class="table-wrapper">

                    <table>

                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Event</th>
                                <th>Pump</th>
                                <th>Duration</th>
                                <th>Water Used</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody id="activityTable">

                            <?php if (mysqli_num_rows($result_activity) > 0): ?>

                                    <?php while ($activity = mysqli_fetch_assoc($result_activity)): ?>

                                            <?php
                                            $event_class = (
                                                $activity["event"] === "Pump Started"
                                            ) ? "event-start" : "event-stop";

                                            $activity_duration = (
                                                $activity["duration"] !== null
                                            ) ? formatRuntime($activity["duration"]) : "--";

                                            $activity_water = (
                                                $activity["water_used"] !== null
                                            ) ? number_format((float) $activity["water_used"], 2) . " L" : "Not measured";
                                            ?>

                                            <tr>

                                                <td>
                                                    <?php echo date("h:i A", strtotime($activity["created_at"])); ?>
                                                </td>

                                                <td class="<?php echo htmlspecialchars($event_class); ?>">
                                                    <?php echo htmlspecialchars($activity["event"]); ?>
                                                </td>

                                                <td>
                                                    <?php echo htmlspecialchars($activity["pump_id"]); ?>
                                                </td>

                                                <td>
                                                    <?php echo htmlspecialchars($activity_duration); ?>
                                                </td>

                                                <td>
                                                    <?php echo htmlspecialchars($activity_water); ?>
                                                </td>

                                                <td>
                                                    <span class="badge success">
                                                        <?php echo htmlspecialchars($activity["status"]); ?>
                                                    </span>
                                                </td>

                                            </tr>

                                    <?php endwhile; ?>

                            <?php else: ?>

                                    <tr>
                                        <td colspan="6">No activity recorded yet.</td>
                                    </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

                <a href="history.php" class="view-history">
                    View Full History →
                </a>

            </div>

            <!-- RECENT ALERTS -->

            <div class="panel alerts-panel">

                <div class="panel-header">
                    <h2>System Alerts</h2>
                </div>

                <?php if (mysqli_num_rows($result_alerts) > 0): ?>

                        <?php while ($alert = mysqli_fetch_assoc($result_alerts)): ?>

                                <?php
                                $alert_type = strtoupper($alert["alert_type"]);

                                if ($alert_type === "CRITICAL") {
                                    $alert_class = "red";
                                    $alert_symbol = "!";
                                } elseif ($alert_type === "WARNING") {
                                    $alert_class = "orange";
                                    $alert_symbol = "!";
                                } else {
                                    $alert_class = "green";
                                    $alert_symbol = "i";
                                }
                                ?>

                                <div class="alert-item">

                                    <div class="alert-icon <?php echo htmlspecialchars($alert_class); ?>">
                                        <?php echo htmlspecialchars($alert_symbol); ?>
                                    </div>

                                    <div>
                                        <strong>
                                            <?php echo htmlspecialchars($alert["title"]); ?>
                                        </strong>

                                        <p>
                                            <?php echo htmlspecialchars($alert["message"]); ?>
                                        </p>
                                    </div>

                                    <span>
                                        <?php echo date("h:i A", strtotime($alert["created_at"])); ?>
                                    </span>

                                </div>

                        <?php endwhile; ?>

                <?php else: ?>

                        <div class="alert-item">
                            <div class="alert-icon green">✓</div>
                            <div>
                                <strong>No alerts available</strong>
                                <p>No recorded alerts for your account.</p>
                            </div>
                        </div>

                <?php endif; ?>

                <a href="alerts.php" class="view-history">
                    View All Alerts →
                </a>

            </div>

        </section>

    </main>

    <script src="js/sidebar.js"></script>

</body>
</html>
