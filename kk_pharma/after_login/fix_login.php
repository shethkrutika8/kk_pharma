<?php
require_once 'config.php';

echo "<h2>Fixing Login Issues</h2>";

// Check if admin table exists
$result = $conn->query("SHOW TABLES LIKE 'admin'");
if ($result->num_rows == 0) {
    echo "Creating admin table...<br>";
    $conn->query("CREATE TABLE admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

// Check if admin user exists
$result = $conn->query("SELECT * FROM admin WHERE username = 'admin'");
if ($result->num_rows == 0) {
    echo "Creating admin user...<br>";
    $password = 'admin123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admin (username, password, email) VALUES 
        ('admin', '$hashed_password', 'admin@pharmacy.com')");
} else {
    echo "Admin user exists. Resetting password...<br>";
    $password = 'admin123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $conn->query("UPDATE admin SET password = '$hashed_password' WHERE username = 'admin'");
}

// Test the password verification
$result = $conn->query("SELECT * FROM admin WHERE username = 'admin'");
$admin = $result->fetch_assoc();

if (password_verify('admin123', $admin['password'])) {
    echo "<br><strong style='color: green;'>✓ Password verification successful!</strong><br>";
} else {
    echo "<br><strong style='color: red;'>✗ Password verification failed!</strong><br>";
}

echo "<br>Login credentials:<br>";
echo "Username: <strong>admin</strong><br>";
echo "Password: <strong>admin123</strong><br><br>";

echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login</a>";
?>
