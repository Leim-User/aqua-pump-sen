<?php

session_start();

if (isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}

$errors = [];

if (isset($_POST["submit"])) {
    $fname = trim($_POST["fname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $passwordRepeat = $_POST["passwordRepeat"] ?? "";

   
    if ($fname === "" || $email === "" || $password === "" || $passwordRepeat === "") {
        $errors[] = "All fields required";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email";
    }

    if (strlen($password) < 8) {
        $errors[] = "At least 8 characters";
    }

    if ($password !== $passwordRepeat) {
        $errors[] = "Password not matched";
    }

    
    if (empty($errors)) {
        require_once __DIR__ . "/backend/config/config.php";

        
        $stmt = mysqli_prepare($connect, "SELECT user_id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "Email already exists";
        }
        mysqli_stmt_close($stmt);
    }

    
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = mysqli_prepare($connect, "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $fname, $email, $passwordHash);
        mysqli_stmt_execute($stmt);

        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Aqua Pump</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="assets/aq.png">
</head>
<body>
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>

    <main class="signup-container">
        <section class="signup-card">

            <div class="signup-heading">
                <h1>Register to the Dashboard</h1>
                <p>Sign up to access your dashboard</p>
            </div>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="fname">Full Name</label>
                    <input type="text" id="fname" name="fname"
                           placeholder="Enter your name">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password">
                </div>

                <div class="form-group">
                    <label for="passwordRepeat">Confirm Password</label>
                    <input type="password" id="passwordRepeat" name="passwordRepeat"
                           placeholder="Confirm your password">
                </div>

                <button type="submit" class="signup-button" name="submit">
                    Sign Up
                </button>

                <div class="form-options">
                    <label class="accountStatus">
                        <span>Already have an account?</span>
                    </label>
                    <a href="login.php">Login</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
