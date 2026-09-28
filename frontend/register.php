<?php
session_start();

if (isset($_SESSION["user"])) {
    header("Location: index.php");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Aqua Pump</title>
    <link rel="stylesheet" href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="assets/aq.png">
</head>
<body>
    <?php

    require_once "../backend/config/config.php";

    $fname = $_POST["fname"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $passwordRepeat = $_POST["passwordRepeat"];

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $errors = [];

    if (isset($_POST["submit"])) {

        if (empty($fname) || empty($email) || empty($password) || empty($passwordRepeat)) {
            array_push($errors, "All fields required");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            array_push($errors, "Invalid email");
        }

        if (strlen($password) < 8) {
            array_push($errors, "At least 8 characters");
        }

        if ($password !== $passwordRepeat) {
            array_push($errors, "Password not matched");
        }

        $sql = "SELECT * FROM users WHERE email = '$email'";
        $query = mysqli_query($connect, $sql);
        $rowCount = mysqli_num_rows($query);

        if ($rowCount > 0) {
            array_push($errors, "Email already exists");
        }

        if (count($errors) > 0) {
            foreach ($errors as $err) {
                echo "<div class='alert alert-danger'>$err</div>";
            }
        }
        else {
            $sql = "INSERT INTO users (full_name, email, password) VALUES (?,?,?)";

            $stmt = mysqli_stmt_init($connect);
            $prepare = mysqli_stmt_prepare($stmt, $sql);

            if ($prepare) {
                mysqli_stmt_bind_param($stmt, "sss", $fname, $email, $passwordHash);
                mysqli_stmt_execute($stmt);

                echo "<div class='alert alert-success'>Registered Successfuly</div>";
                header("Location: login.php");
            }
            else {
                dir("Something went wrong!!!");
            }
        }
    }


    ?>

    <main class="signup-container">
        <section class="signup-card">

            <div class="signup-heading">
                <h1>Register to the Dashboard</h1>
                <p>Sign up to access your dashboard</p>
            </div>

            <form action="register.php"  method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="fname"
                           placeholder="Enter your name">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email"
                           placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password"
                           placeholder="Enter your password">
                </div>

                <div class="form-group">
                    <label for="password">Confirm Password</label>
                    <input type="password" name="passwordRepeat"
                           placeholder="Confirm Password password">
                </div>
                
                <button type="submit" class="signup-button" name="submit">
                    Sign Up
                </button>

                <div class="form-options">
                    <label class="accountStatus">
                        <span>Already have an account?</span>
                    </label>
                    <a href="login.php" id="register">Login</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
