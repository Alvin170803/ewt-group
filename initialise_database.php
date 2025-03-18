<?php 
/**
 * Simple Database Setup Script
 * 
 * Creates a database and user_registration table with basic attributes
 */

// Database configuration 
$db_host = "localhost";
$db_user = "root";  // change if needed
$db_pass = "";      // change if needed
$db_name = "user_manangement";

// Create connection to MySQL without selecting a database
try {
    $conn = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS $db_name";
    $conn->exec($sql);
    echo "Database created successfully<br>";
    
    // Connect to the new database
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create user_registration table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        profile_pic VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "Table users created successfully";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close connection
$conn = null;
?>