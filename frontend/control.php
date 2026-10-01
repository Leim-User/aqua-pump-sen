<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require "../backend/config/config.php";

// Store user's details
$user_id = $_SESSION["user_id"];

$pump_id = 1;



// GET THE CURRENT PUMP STATE

$sql = "SELECT pump_id, pump_name, status FROM pumps WHERE pump_id = ?";

$stmt = mysqli_prepare($connect, $sql);

mysqli_stmt_bind_param($stmt, "i", $pump_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$pump_assoc = mysqli_fetch_assoc($result);


// CHECK IF THE PUMP EXISTS

if (!$pump_assoc) {
    die("Pump not found");
}

$current_status = $pump_assoc["status"];


// PROCESS THE PUMP COMMAND

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";


    // START THE PUMP

    if ($action === "start") {
        if ($current_status === "RUNNING") {

            $_SESSION["pump_message"] = "Pumpis already running";
        } else {

            $new_status = "RUNNING";


            // Update pump state

            $sql_update = "UPDATE pumps SET status = ? WHERE pump_id = ?";

            $stmt_update = mysqli_prepare($connect, $sql_update);

            mysqli_stmt_bind_param($stmt_update, "si", $new_status, $pump_id);

            mysqli_stmt_execute($stmt_update);

            // RECORD EVENT IN HOSTORY
            $event = "Pump Started";
            $status = "Completed";

            $sql_history = "INSERT INTO history (user_id, pump_id, event, duration, water_used, status) VALUES (?,?,?, NULL, NULL, ?)";

            $stmt_history = mysqli_prepare($connect, $sql_history);
            mysqli_stmt_bind_param($stmt_history, "isss", $pump_id, $event, $status);

            mysqli_stmt_execute($stmt_history);

            $_SESSION["control_message"] = "Pump started successfully.";
        }
    }


    // STOP THE PUMP
    elseif ($action === "stop") {

        if ($current_status === "STOPPED") {

            $_SESSION["control_message"] = "Pump already stopped.";
        } else {

            $new_status = "STOPPED";


            // Update  pump status

            $sql_update = "UPDATE pumps SET status = ? WHERE pump_id = ?";

            $stmt_update = mysqli_prepare($connect, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "si", $new_status, $pump_id);

            mysqli_stmt_execute($stmt_update);



            // TEMPORARY TEST VALUES 
            // The actual duration and water usage will come from the pump, sensors and controllers.


            $duration = 15;
            $water_used = 80;

            $event = "Pump Stopped";
            $status = "Completed";

            // Record the event in history
            $sql_history = "INSERT INTO history (user_id, pump_id, event, duration, water_used, status) VALUES (?,?,?,?,?,?)";

            $stmt_history = mysqli_prepare($connect, $sql_history);
            mysqli_stmt_bind_param($stmt_history, "iisiis", $user_id, $pump_id, $event, $duration, $water_used, $status);

            mysqli_stmt_execute($stmt_history);

            $_SESSION["control_messsage"] = "Pump stopped successfully.";
        }
    }

    // IF PUMP COMMAND IS UNKNOWN
    else {

        $_SESSION["control_message"] = "Invalid pump command";
    }

    // PREVENT FORM RESUBMISSION

    header("Location: control.php");
    exit();
}


// DISPLAY THE MESSAGE STORED IN THE $_SESSION SUPERGLOBAL ARRAY

$control_message = $_SESSION["control_message"] ?? "";
unset($control_message);


// REFRESH PUMP STATE AFTER COMMAND

$sql_state = "SELECT pump_id, pump_name, status FROM pumps WHERE pump_id = ?";

$stmt_state = mysqli_prepare($connect, $sql_state);
mysqli_stmt_bind_param($stmt_state, "i", $pump_id);
mysqli_stmt_execute($stmt_state);

$result_state = mysqli_stmt_get_result($stmt_state);

$pump_assoc = mysqli_fetch_assoc($result_state);

$current_status = $pump_assoc["status"];

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
            <a href="index.php" class="nav-link active">
                <span>&gt;</span> Dashboard
            </a>
            <a href="control.php" class="nav-link ">
                <span>&gt;</span> Control Panel
            </a>
            <a href="history.php" class="nav-link ">
                <span>&gt;</span> History
            </a>
            <a href="alerts.php" class="nav-link ">
                <span>&gt;</span> Alerts &amp; System
            </a>
        </nav>

        <div class="sidebar-status">
            <div class="status-icon">●</div>
            <div>
                <strong>System Status</strong>
                <p>All systems normal</p>
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
                    <span class="online-dot"></span> System Online
                </div>
                <div class="notification">🔔 <span class="notification-count">0</span></div>
                <div class="date-time">
                    <strong id="currentDate">14 Sep 2026</strong>
                    <span id="currentTime">12:00 AM</span>
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
                <p>Monitor and control the pumping system</p>
            </div>
        </section>

        <section class="control-page-grid">
            <div class="panel large-control-panel">
                <div class="panel-header">
                    <div>
                        <h2>Pump Control</h2>
                        <span>Commands will later be sent through the PHP backend</span>
                    </div>
                </div>

                <div class="pump-state">
                    <span class="pump-state-label">Current Pump State</span>
                    <strong id="controlPumpState">
                        <?php echo htmlspecialchars($current_status); ?>
                    </strong>
                </div>

                <div class="pump-buttons">
                    <form action="control.php" method="POST">
                        <input type="hidden" name="action" value="start">

                        <button class="control-button start-button" type="submit">START PUMP</button>
                    </form>

                    <form action="control.php" method="POST">
                        <input type="hidden" name="action" value="stop">

                        <button class="control-button stop-button" type="submit">STOP PUMP</button>
                    </form>
                </div>

                <div class="control-message" id="controlPageMessage">
                    <?php if (!empty($control_message)): ?>
                        <p class="alert alert-info"><?php echo htmlspecialchars($control_message); ?></p>
                    <?php else: ?>
                        <p class="alert alert-info">Pump is ready for operation</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <div>
                        <h2>Safety Conditions</h2>
                        <span>Before a real command is accepted</span>
                    </div>
                </div>

                <div class="condition-list">
                    <div class="condition-item">
                        <div><strong>Water Level</strong><span>1875 / 2500 L</span></div>
                        <strong class="condition-ok">SAFE</strong>
                    </div>
                    <div class="condition-item">
                        <div><strong>Pump Availability</strong><span>Pump ready</span></div>
                        <strong class="condition-ok">AVAILABLE</strong>
                    </div>
                    <div class="condition-item">
                        <div><strong>System Connection</strong><span>ESP32 controller</span></div>
                        <strong class="condition-ok">ONLINE</strong>
                    </div>
                    <div class="condition-item">
                        <div><strong>Safety Status</strong><span>No active fault</span></div>
                        <strong class="condition-ok">NORMAL</strong>
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
                    <div class="sensor-card"><span>Water Level</span><strong>1875 L</strong></div>
                    <div class="sensor-card"><span>Flow Rate</span><strong>0 L/min</strong></div>
                    <div class="sensor-card"><span>Pump</span><strong id="readingPump">
                            <?php echo htmlspecialchars($current_status); ?>
                        </strong></div>
                    <div class="sensor-card"><span>Controller</span><strong>ESP32</strong></div>
                </div>
            </div>

            <div class="panel control-notice">
                <div class="notice-icon">!</div>
                <div>
                    <strong>Safety Notice</strong>
                    <p>
                        In the final system, the PHP backend will validate authentication,
                        authorization, water level, pump availability and safety conditions
                        before sending a command to the ESP32.
                    </p>
                </div>
            </div>
        </section>

    </main>
</body>

</html>