<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../backend/config/config.php";

$user_id = $_SESSION["user_id"];
$pump_id = 1;

// Retrieve the latest pump state.
// This page must NEVER modify the pump state.

$sql = "SELECT pump_id, pump_name, status
        FROM pumps
        WHERE pump_id = ?";

$stmt = mysqli_prepare($connect, $sql);

mysqli_stmt_bind_param($stmt, "i", $pump_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$pump_assoc = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$pump_assoc) {
    die("Pump not found.");
}

$current_status = $pump_assoc["status"];

mysqli_close($connect);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Panel | Aqua Pump</title>
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
            <a href="control.php" class="nav-link active">
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
                <p>Monitoring active</p>
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
                    <span class="online-dot" id="deviceOnlineDot"></span>
                    <span id="deviceOnlineText">Checking connection...</span>
                </div>
                <div class="notification">🔔 <span class="notification-count">0</span></div>
                <div class="date-time">
                    <strong id="currentDate"><?php echo date("d/M/Y"); ?></strong>
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
                <h1>Control Panel</h1>
                <p>Monitor the automatic pumping system and its operations</p>
            </div>
        </section>

        <section class="control-page-grid">

            <div class="panel large-control-panel">

                <div class="panel-header">
                    <div>
                        <h2>Automatic Pump Monitoring</h2>
                        <span>
                            Pump operation is managed by the ESP32 controller.
                        </span>
                    </div>
                </div>

                <div class="pump-state">
                    <span class="pump-state-label">
                        Current Pump State
                    </span>

                    <strong id="controlPumpState">
                        <?php echo htmlspecialchars($current_status); ?>
                    </strong>
                </div>

                <div class="control-message">
                    <p class="alert alert-info">
                        Automatic filling mode is active.
                        The ESP32 independently controls the pump
                        according to sensor readings and safety conditions.
                        Manual pump control is currently disabled.
                    </p>
                </div>

            </div>

            <div class="panel">
                <div class="panel-header">
                    <div>
                        <h2>Safety Conditions</h2>
                        <span>Automatic operating conditions</span>
                    </div>
                </div>

                <div class="condition-list">
                    <div class="condition-item">
                        <div>
                            <strong>Container Detection</strong>
                            <span>IR sensor monitoring</span>
                        </div>
                        <strong id="safetyContainer">--</strong>
                    </div>

                    <div class="condition-item">
                        <div>
                            <strong>Ultrasonic Sensor</strong>
                            <span>Distance monitoring</span>
                        </div>
                        <strong id="safetyUltrasonic">--</strong>
                    </div>

                    <div class="condition-item">
                        <div>
                            <strong>System Connection</strong>
                            <span>ESP32 controller</span>
                        </div>
                        <strong id="safetyConnection">--</strong>
                    </div>

                    <div class="condition-item">
                        <div>
                            <strong>Safety Status</strong>
                            <span>Controller fault monitoring</span>
                        </div>
                        <strong id="safetyFault">--</strong>
                    </div>
                </div>
            </div>

        </section>

        <section class="control-page-grid">

            <div class="panel">
                <div class="panel-header">
                    <div>
                        <h2>Current Readings</h2>
                        <span>Sensor and controller values</span>
                    </div>
                </div>

                <div class="sensor-grid">

                    <div class="sensor-card">
                        <span>Ultrasonic Distance</span>
                        <strong id="readingDistance">-- cm</strong>
                    </div>

                    <div class="sensor-card">
                        <span>Container Detection</span>
                        <strong id="readingContainer">--</strong>
                    </div>

                    <div class="sensor-card">
                        <span>Pump Status</span>
                        <strong id="readingPump">
                            <?php echo htmlspecialchars($current_status); ?>
                        </strong>
                    </div>

                    <div class="sensor-card">
                        <span>Controller</span>
                        <strong id="readingController">OFFLINE</strong>
                    </div>

                </div>

                <p class="text-muted mt-3" id="lastTelemetry">
                    Waiting for ESP32 telemetry...
                </p>
            </div>

            <div class="panel control-notice">
                <div class="notice-icon">!</div>
                <div>
                    <strong>Safety Notice</strong>
                    <p>
                        The ESP32 independently manages automatic pump operation.
                        The dashboard is currently monitoring sensor readings
                        and pump status. No manual pump commands are available.
                    </p>
                </div>
            </div>

        </section>

    </main>

    <script src="js/sidebar.js"></script>
    <script src="js/device-monitor.js"></script>

</body>

</html>