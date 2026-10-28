<?php
session_start();

if (isset($_SESSION["user"]) || isset($_SESSION["user_id"])) {
    header("Location: frontend/index.php");
} else {
    header("Location: frontend/login.php");
}
exit();
