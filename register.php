<?php
// Database connection
include "databasecredentials.php";
// var_dump($username, $password);
// Handle registration
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $profile_pic = $_FILES["profile_pic"];

    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($profile_pic)) {
        $message = "All fields are required!";
    } elseif ($profile_pic["size"] > 5 * 1024 * 1024) {
        $message = "Profile picture must be less than 5MB!";
    } else {
        // Secure password hashing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Handle profile picture upload
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = pathinfo($profile_pic["name"], PATHINFO_EXTENSION);
        $allowed_exts = ["jpg", "jpeg", "png", "gif"];

        if (!in_array(strtolower($file_ext), $allowed_exts)) {
            $message = "Only JPG, JPEG, PNG, and GIF files are allowed!";
        } else {
            $file_name = uniqid() . "." . $file_ext;
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($profile_pic["tmp_name"], $file_path)) {
                $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
                // $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_user, $db_password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // Insert into database
                $sql = "INSERT INTO users (username, email, password, profile_pic) VALUES (:username, :email, :password, :profile_pic)";
                $stmt = $conn->prepare($sql);

                try {
                    $stmt->execute([
                        ':username' => $username,
                        ':email' => $email,
                        ':password' => $hashed_password,
                        ':profile_pic' => $file_path
                    ]);
                    echo '<script>
                            document.addEventListener("DOMContentLoaded", function() {
                                var successModal = new bootstrap.Modal(document.getElementById("successModal"));
                                successModal.show();
                                setTimeout(function() {
                                    window.location.href = "login.php";
                                }, 3000);
                            });
                        </script>';
                       
                } catch (PDOException $e) {
                    $message = "Email already exists!";
                }
            } else {
                $message = "Failed to upload profile picture!";
            }
        }
    }
}

// // Fetch registered users
// $sql = "SELECT id, username, email, profile_pic, created_at FROM users ORDER BY created_at DESC";
// $stmt = $conn->query($sql);
// $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <!-- Success Modal -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-success" id="successModalLabel">Success</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Registration successful! Redirecting to login page...
                    </div>
                </div>
            </div>
        </div>


    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4">
                        <h2 class="text-center text-primary">User Registration</h2>

                        <?php if (!empty($message)) echo "<p class='text-danger text-center'>$message</p>"; ?>   

                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Username:</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email:</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password:</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Profile Picture (Max: 5MB):</label>
                                <input type="file" name="profile_pic" class="form-control" accept="image/*" required>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary w-100">Register</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <p>Already signed up? <a href="login.php" class="text-primary">Log in</a></p>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        
    </div>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>