<?php 
// Start the session
session_start();

// Include database credentials to update the token in the DB
include "databasecredentials.php";

// If the user is logged in and session exists
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    $user_id = $_SESSION["user_id"];

    try {
        // Create a new PDO connection
        $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Invalidate the remember token and expiry in the database for this user
        $updateStmt = $conn->prepare("UPDATE users SET remember_token = NULL, remember_expiry = NULL WHERE id = :id");
        $updateStmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $updateStmt->execute();
    } catch (PDOException $e) {
        // Log error if needed
        error_log("Error clearing remember me token: " . $e->getMessage());
    }
}

// Clear the "remember_me" cookie if it exists
if (isset($_COOKIE["remember_me"])) {
    setcookie("remember_me", "", time() - 3600, "/", "", false, true); // Secure and HTTPOnly
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Start a new session to store the logout message
session_start();
$_SESSION['logout_message'] = "Logout successful!";

// Redirect to login page
header("Location: login.php");
exit();
?>
