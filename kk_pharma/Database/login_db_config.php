<?php
// Login Database Configuration
// This file handles all login-related database operations

// Connect to database
$con = mysqli_connect("localhost", "root", "");

// Check connection
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Create database if not exists
$create_db = "CREATE DATABASE IF NOT EXISTS kk_pharma_login";
if (!mysqli_query($con, $create_db)) {
    die("Database creation failed: " . mysqli_error($con));
}

// Select database
if (!mysqli_select_db($con, "kk_pharma_login")) {
    die("Database selection failed: " . mysqli_error($con));
}

// Create users table for login system
$create_users_table = "CREATE TABLE IF NOT EXISTS login_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'user') DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email)
)";

if (mysqli_query($con, $create_users_table)) {
    // Insert default admin if table is empty
    $check_admin = mysqli_query($con, "SELECT id FROM login_users WHERE user_type = 'admin'");
    if (mysqli_num_rows($check_admin) == 0) {
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = "INSERT INTO login_users (username, email, password, user_type) VALUES ('admin', 'admin@kkpharma.com', '$hashed_password', 'admin')";
        mysqli_query($con, $insert_admin);
    }
}

// Function to authenticate user
function authenticate_user($email_or_username, $password) {
    global $con;
    
    $stmt = $con->prepare("SELECT id, username, email, password, user_type FROM login_users WHERE (email = ? OR username = ?) AND is_active = 1 LIMIT 1");
    $stmt->bind_param("ss", $email_or_username, $email_or_username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Use password_verify for hashed passwords, fallback to plain text for legacy
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            // If password is plain text, hash it now
            if ($password === $user['password'] && !password_verify($password, $user['password'])) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $con->prepare("UPDATE login_users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user['id']);
                $update_stmt->execute();
            }
            return $user;
        }
    }
    
    return false;
}

// Function to create new user
function create_login_user($username, $email, $password, $user_type = 'user') {
    global $con;
    
    $stmt = $con->prepare("INSERT INTO login_users (username, email, password, user_type) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    
    if ($stmt->execute()) {
        return $con->insert_id;
    }
    
    return false;
}

// Function to update user password
function update_user_password($email, $new_password) {
    global $con;
    
    $stmt = $con->prepare("UPDATE login_users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE email = ?");
    $stmt->bind_param("ss", $new_password, $email);
    
    return $stmt->execute();
}

// Function to get user by email
function get_user_by_email($email) {
    global $con;
    
    $stmt = $con->prepare("SELECT * FROM login_users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

?>
