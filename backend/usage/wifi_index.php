<?php

$cmdFile = __DIR__ . '/command.txt';
$lvlFile = __DIR__ . 'level.txt';

// Save the command when a button is clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['PUMP'] ?? '', ['ON', 'OFF'], true)) {
    file_put_contents($cmdFile, $_POST['pump'], LOCK_EX);
}

$command = is_file($cmdFile) ? file_get_contents($cmdFile) : 'OFF';
$last = is_file($lvlFile) ? file_get_contents($lvlFile) : 'nothing received yet.';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP32 Test</title>
</head>

<body>
    <h2>ESP32 Connection Test</h2>
    <p>Last readings: <strong><?= htmlspecialchars($last) ?></strong></p>
    <p>Current Command: <strong><?= htmlspecialchars($command) ?></strong></p>
    <form action="" method="post">
        <button name="pump" value="ON">ON</button>
        <button name="pump" value="OFF">OFF</button>
    </form>
</body>

</html>