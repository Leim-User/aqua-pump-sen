<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../backend/config/config.php";

$user_id = $_SESSION["user_id"];


// HISTORY TABLE: Select logs from DB table where user_id = current logged-in user

$sql = "SELECT history_id, pump_id, event, duration, water_used, status, created_at FROM history WHERE user_id = ? ORDER BY created_at DESC";


// Prepare Statement
$stmt = mysqli_prepare($connect, $sql);

mysqli_stmt_bind_param($stmt, "i", $user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


// TOTAL WATEER USAGE
$sql_today = "SELECT COALESCE(SUM(water_used), 0) AS total_water FROM history WHERE user_id = ? AND DATE(created_at) = CURDATE()";

$stmt_dayWater = mysqli_prepare($connect, $sql_today);
mysqli_stmt_bind_param($stmt_dayWater, "i", $user_id);
mysqli_stmt_execute($stmt_dayWater);

$result_today = mysqli_stmt_get_result($stmt_dayWater);
$total_assoc_dayData = mysqli_fetch_assoc($result_today);
$total_water = $total_assoc_dayData["total_water"];


// TOTAL OPERATIONS
$sql_opns = "SELECT COUNT(*) AS total_opns FROM history WHERE user_id = ? AND DATE(created_at) = CURDATE() AND event = 'Pump Stopped'";

$stmt_opns = mysqli_prepare($connect, $sql_opns);
mysqli_stmt_bind_param($stmt_opns, "i", $user_id);
mysqli_stmt_execute($stmt_opns);

$result_opns = mysqli_stmt_get_result($stmt_opns);
$total_assoc_opns = mysqli_fetch_assoc($result_opns);
$total_opns = $total_assoc_opns["total_opns"];


// TOTAL RUNTIME
$sql_runtime = "SELECT COALESCE(SUM(duration), 0) AS total_runtime FROM history WHERE user_id = ? AND DATE(created_at) = CURDATE()";

$stmt_runtime = mysqli_prepare($connect, $sql_runtime);
mysqli_stmt_bind_param($stmt_runtime, "i", $user_id);
mysqli_stmt_execute($stmt_runtime);

$result_runtime = mysqli_stmt_get_result($stmt_runtime);
$total_assoc_runtime = mysqli_fetch_assoc($result_runtime);
$total_runtime = $total_assoc_runtime["total_runtime"];

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>History | Aqua Pump</title>
        <link rel="stylesheet" href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
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
                    <h1>History</h1>
                    <p>Review recent pump operations and water usage</p>
                </div>
            </section>

            <section class="panel history-panel">
                <div class="history-summary">
                    <div class="history-summary-item">
                        <span>Total Water Used Today</span>
                        <strong>
                            <?php echo htmlspecialchars($total_water) . "L"; ?>
                        </strong>
                    </div>
                    <div class="history-summary-item">
                        <span>Pump Operations</span>
                        <strong>
                             <?php echo htmlspecialchars($total_opns); ?>
                        </strong>
                    </div>
                    <div class="history-summary-item">
                        <span>Total Runtime</span>
                        <strong>
                             <?php echo htmlspecialchars($total_runtime) . "min"; ?>
                        </strong>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Event</th>
                                <th>Pump ID</th> <!-- Willbe replaced by Pump, showing which pump performed the operation -->
                                <th>Duration</th>
                                <th>Water Used</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <?php 
                                        $timestamp = strtotime($row["created_at"]);
                                        
                                        $date = date("d M Y", $timestamp);

                                        $time = date("h:i A", $timestamp);
                                    ?>

                                    <tr>

                                        <td>
                                            <?php echo htmlspecialchars($date); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($time); ?>
                                        </td>

                                        <td class="<?php 
                                            echo $row["event"] === "Pump Started"
                                            ? "event-start"
                                            : "event-stop";
                                        ?>">
                                            <?php echo htmlspecialchars($row["event"]); ?>    
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($row["pump_id"]); ?>
                                        </td>

                                        <td>
                                            <?php 
                                                if ($row["duration"] !== null) {
                                                    echo htmlspecialchars($row["duration"]) . "min";
                                                } else {
                                                    echo "--";
                                                }
                                            ?>
                                        </td>

                                        <td>
                                            <?php 
                                                if ($row["water_used"] !== null) {
                                                    echo htmlspecialchars($row["water_used"]) . "L";
                                                } else {
                                                    echo "--";
                                                }
                                            ?>
                                        </td>

                                        <td>
                                            <span class="badge success">
                                                <?php echo htmlspecialchars($row["status"]); ?>
                                            </span>
                                            
                                        </td>

                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>

                                <tr>
                                    <td colspan="7" class="text-center">
                                        No pump history available.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </body>
</html>