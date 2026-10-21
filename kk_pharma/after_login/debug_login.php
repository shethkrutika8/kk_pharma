<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Login Debug Information</h2>";

try {
    // Test database connection
    $conn = new mysqli('localhost', 'root', '', 'pharmacy_db');
    
    if ($conn->connect_error) {
        echo "<strong style='color: red;'>Database Connection Failed:</strong> " . $conn->connect_error . "<br>";
    } else {
        echo "<strong style='color: green;'>✓ Database Connected Successfully</strong><br>";
        
        // Check if admin table exists
        $result = $conn->query("SHOW TABLES LIKE 'admin'");
        if ($result->num_rows > 0) {
            echo "<strong style='color: green;'>✓ Admin table exists</strong><br>";
            
            // Check if admin user exists
            $result = $conn->query("SELECT * FROM admin WHERE username = 'admin'");
            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                echo "<strong style='color: green;'>✓ Admin user found</strong><br>";
                echo "Admin ID: " . $admin['id'] . "<br>";
                echo "Username: " . $admin['username'] . "<br>";
                echo "Password Hash: " . substr($admin['password'], 0, 20) . "...<br>";
                
                // Test password verification
                if (password_verify('admin123', $admin['password'])) {
                    echo "<strong style='color: green;'>✓ Password verification SUCCESSFUL</strong><br>";
                } else {
                    echo "<strong style='color: red;'>✗ Password verification FAILED</strong><br>";
                    echo "Trying to create new password hash...<br>";
                    
                    // Create new password hash
                    $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
                    $conn->query("UPDATE admin SET password = '$new_hash' WHERE username = 'admin'");
                    
                    // Test again
                    $result = $conn->query("SELECT * FROM admin WHERE username = 'admin'");
                    $admin = $result->fetch_assoc();
                    
                    if (password_verify('admin123', $admin['password'])) {
                        echo "<strong style='color: green;'>✓ New password hash works!</strong><br>";
                    } else {
                        echo "<strong style='color: red;'>✗ Still having issues with password</strong><br>";
                    }
                }
            } else {
                echo "<strong style='color: red;'>✗ Admin user not found</strong><br>";
                echo "Creating admin user...<br>";
                
                $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
                $conn->query("INSERT INTO admin (username, password, email) VALUES 
                    ('admin', '$hashed_password', 'admin@pharmacy.com')");
                echo "<strong style='color: green;'>✓ Admin user created</strong><br>";
            }
        } else {
            echo "<strong style='color: red;'>✗ Admin table does not exist</strong><br>";
            echo "Creating admin table...<br>";
            
            $conn->query("CREATE TABLE admin (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
            $conn->query("INSERT INTO admin (username, password, email) VALUES 
                ('admin', '$hashed_password', 'admin@pharmacy.com')");
            echo "<strong style='color: green;'>✓ Admin table and user created</strong><br>";
        }
        
        $conn->close();
    }
    
    echo "<br><strong>Login Credentials:</strong><br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br><br>";
    
    echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Try Login Now</a>";
    
} catch (Exception $e) {
    echo "<strong style='color: red;'>Error:</strong> " . $e->getMessage() . "<br>";
}
?>
