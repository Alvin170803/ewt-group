<?php  
// Start session securely
session_start();
include "databasecredentials.php";

// Auto-login with remember_me token if session is not set
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
                // Token is valid, recreate session securely
                session_regenerate_id(true);
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["profile_pic_url"] = $user["profile_pic"];
                $_SESSION["logged_in"] = true;
                break;
            }
        }
    } catch (PDOException $e) {
        error_log("Remember Me auto-login error: " . $e->getMessage());
    }
}

// Redirect to login if not logged in
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

// Pull user data from session
$username = $_SESSION["username"];
$email = $_SESSION["user_email"]; 
$user_id = $_SESSION["user_id"];
$profile_pic = $_SESSION["profile_pic_url"];

// Default profile picture if none exists
if (empty($profile_pic)) {
    $profile_pic = "default-profile.jpg";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script>
        function updateTime() {
            const now = new Date();
            document.getElementById("current-time").innerText = now.toLocaleTimeString();
        }

        document.addEventListener("DOMContentLoaded", function() {
            updateTime();
            setInterval(updateTime, 1000);
        });
    </script>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">Home</span>
            <div class="d-flex">
                <a href="" class="btn btn-outline-light me-2">View Profile</a>
                <a href="logout.php" class="btn btn-light text-primary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg border-0">
                    <div class="card-body text-center p-4">
                        <img src="<?php echo "uploads/" . htmlspecialchars($profile_pic); ?>" 
                             alt="Profile Picture" class="rounded-circle mb-3" width="100" height="100">
                        <h3 class="text-primary">Welcome, <?php echo htmlspecialchars($username); ?>!</h3>
                        <p class="text-muted">Email: <?php echo htmlspecialchars($email); ?></p>
                        <h5 class="mt-4">Current Time:</h5>
                        <p id="current-time" class="fw-bold fs-4 text-primary">--:--:--</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
