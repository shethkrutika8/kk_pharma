<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Simple Login Debug</h2>";

// Test basic database connection
try {
    $conn = new mysqli('localhost', 'root', '', 'pharmacy_db');
    
    if ($conn->connect_error) {
        echo "<div style='color: red; padding: 10px; background: #ffe6e6; border: 1px solid red;'>";
        echo "<strong>Database Connection Failed:</strong><br>";
        echo $conn->connect_error;
        echo "</div>";
        
        echo "<br>Trying to create database...<br>";
        
        // Try to create database
        $conn2 = new mysqli('localhost', 'root', '');
        $conn2->query("CREATE DATABASE IF NOT EXISTS pharmacy_db");
        $conn2->close();
        
        echo "Database creation attempted. Please refresh this page.<br>";
        
    } else {
        echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green;'>";
        echo "<strong>✓ Database Connected Successfully</strong>";
        echo "</div><br>";
        
        // Check admin table
        $tables = $conn->query("SHOW TABLES LIKE 'admin'");
        if ($tables->num_rows == 0) {
            echo "<div style='color: orange; padding: 10px; background: #fff3cd; border: 1px solid orange;'>";
            echo "Creating admin table...<br>";
            $conn->query("CREATE TABLE admin (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            echo "Admin table created.<br>";
            echo "</div>";
        }
        
        // Check admin user
        $users = $conn->query("SELECT * FROM admin WHERE username = 'admin'");
        if ($users->num_rows == 0) {
            echo "<div style='color: orange; padding: 10px; background: #fff3cd; border: 1px solid orange;'>";
            echo "Creating admin user...<br>";
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $conn->query("INSERT INTO admin (username, password, email) VALUES ('admin', '$hash', 'admin@pharmacy.com')");
            echo "Admin user created with password: admin123<br>";
            echo "</div>";
        } else {
            echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green;'>";
            echo "<strong>✓ Admin user exists</strong><br>";
            
            // Test password
            $admin = $users->fetch_assoc();
            if (password_verify('admin123', $admin['password'])) {
                echo "<strong>✓ Password works!</strong><br>";
            } else {
                echo "<strong>✗ Password verification failed</strong><br>";
                echo "Updating password...<br>";
                $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
                $conn->query("UPDATE admin SET password = '$new_hash' WHERE username = 'admin'");
                echo "Password updated.<br>";
            }
            echo "</div>";
        }
        
        $conn->close();
        
        echo "<br><div style='padding: 10px; background: #f0f0f0; border: 1px solid #ccc;'>";
        echo "<strong>Login Credentials:</strong><br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br><br>";
        echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Try Login Now</a>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; background: #ffe6e6; border: 1px solid red;'>";
    echo "<strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>
