<?php
// Receive a reading from the ESP32 (if sent)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['level'])) {
    $level = (int) $_POST['level'];
    file_put_contents(__DIR__ . '/level.txt', $level . ' (at ' . date('H:i:s') . ')', LOCK_EX);
}

// Reply with the current command (runs on every request)
header('Content-Type: text/plain');
$file = __DIR__ . '/command.txt';
echo is_file($file) ? trim(file_get_contents($file)) : 'OFF';