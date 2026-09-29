<?php
session_start();

if (isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login | Aqua Pump</title>
        <link rel="stylesheet" href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="css/style.css">
        <link rel="icon" href="assets/aq.png">
    </head>
    <body>
    <?php

    if (isset($_POST["login"])) {
        $email = $_POST["email"];
        $password = $_POST["password"];

        require_once "../backend/config/config.php";

        $sql = "SELECT * FROM users WHERE email = ?";
        
        // Prepared statement
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        // Get the result of the statement
        $result = mysqli_stmt_get_result($stmt);

        // Now let's convert the result into an associative array
        $assResult = mysqli_fetch_assoc($result);

        if ($assResult) {
            if(password_verify($password, $assResult["password"])) {
                $_SESSION["user"] = "yes";
                $_SESSION["user_id"] = $assResult["user_id"];
                header("Location: index.php");
                exit();
            }
            else {
                 echo "<div class='alert alert-danger'>Password not matched</div>";
            }
        }
        else {
            echo "<div class='alert alert-danger'>Email does not exist</div>";
        }
    }



    ?>
        <main class="login-container">
            <section class="login-card">

                <div class="login-heading">
                    <h2>Login Form</h2>
                    <p>Sign in to access your dashboard</p>
                </div>

                <form action="login.php" method="POST">
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
                    
                    <button type="submit" class="login-button" name="login">
                        Sign In
                    </button>

                    <div class="form-options">
                        <label class="accountStatus">
                            <span>Don't yet have an account?</span>
                        </label>
                        <a href="register.php" name="register">Register</a>
                    </div>
                </form>
            </section>
        </main>
    </body>
</html>
