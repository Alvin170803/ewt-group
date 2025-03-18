<?php 
session_start();
include "databasecredentials.php";
$message = "";

// Auto-login via Remember Me cookie only if no one is logged in
if (!isset($_SESSION["logged_in"]) && isset($_COOKIE["remember_me"])) {
    try {
        $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $token = $_COOKIE["remember_me"];
        $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token IS NOT NULL AND remember_expiry > NOW()");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($users as $user) {
            if (password_verify($token, $user['remember_token'])) {
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["profile_pic_url"] = $user["profile_pic"];
                $_SESSION["logged_in"] = true;

                header("Location: home.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        error_log("Remember Me auto-login error: " . $e->getMessage());
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Always reset any session to avoid leaking previous user
    session_unset();
    session_destroy();
    session_start();

    $email = filter_var(htmlspecialchars($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"]; 

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (empty($password)) {
        $message = "Please enter your password.";
    } else {
        try {
            $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($password, $user["password"])) {
                    session_regenerate_id(true); // Prevent session fixation
                    // Set session variables
                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["username"] = $user["username"];
                    $_SESSION["user_email"] = $user["email"];
                    $_SESSION["profile_pic_url"] = $user["profile_pic"];
                    $_SESSION["logged_in"] = true;

                    // Remember Me logic
                    if (isset($_POST['remember'])) {
                        $token = bin2hex(random_bytes(16));
                        $expiryTime = time() + (3600 * 30); // 30 minutes expiry for safety
                        $expiry = date('Y-m-d H:i:s', $expiryTime);
                        $hashedToken = password_hash($token, PASSWORD_DEFAULT);

                        // Clear any other existing remember tokens for all users
                        $clearStmt = $conn->prepare("UPDATE users SET remember_token = NULL, remember_expiry = NULL WHERE id != :current_id");
                        $clearStmt->bindParam(':current_id', $user["id"], PDO::PARAM_INT);
                        $clearStmt->execute();

                        // Store token for the current user
                        $updateStmt = $conn->prepare("UPDATE users SET remember_token = :token, remember_expiry = :expiry WHERE id = :id");
                        $updateStmt->bindParam(':token', $hashedToken, PDO::PARAM_STR);
                        $updateStmt->bindParam(':expiry', $expiry, PDO::PARAM_STR);
                        $updateStmt->bindParam(':id', $user["id"], PDO::PARAM_INT);
                        $updateStmt->execute();

                        setcookie("remember_me", $token, $expiryTime, "/", "", false, true);
                    } else {
                        // Optional: Clear token if Remember Me not checked
                        $clearToken = $conn->prepare("UPDATE users SET remember_token = NULL, remember_expiry = NULL WHERE id = :id");
                        $clearToken->bindParam(':id', $user["id"], PDO::PARAM_INT);
                        $clearToken->execute();

                        setcookie("remember_me", "", time() - 3600, "/", "", false, true);
                    }

                    header("Location: home.php");
                    exit();
                } else {
                    $message = "Invalid email or password.";
                }
            } else {
                $message = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $message = "An error occurred during login. Please try again.";
            error_log("Login Error: " . $e->getMessage());
        }
    }
}
?>
<?php

if (isset($_SESSION['logout_message'])) {
    echo '<div class="alert alert-success text-center">' . $_SESSION['logout_message'] . '</div>';
    unset($_SESSION['logout_message']); //  message clears after displaying once
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4">
                        <h2 class="text-center text-primary">Login</h2>
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-danger text-center"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember Me</label>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary w-100">Login</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <p>Don't have an account? <a href="register.php" class="text-primary">Sign up</a></p>
                            <p><a href="forgot-password.php" class="text-secondary">Forgot Password?</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
