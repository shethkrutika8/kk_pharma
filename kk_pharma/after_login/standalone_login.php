<?php
// Start session manually
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0;'>";
    echo "<strong>Login Attempt:</strong><br>";
    echo "Username: '$username'<br>";
    echo "Password: '$password'<br>";
    echo "</div>";
    
    // Direct database connection
    try {
        $conn = new mysqli('localhost', 'root', '', 'pharmacy_db');
        
        if ($conn->connect_error) {
            echo "<div style='color: red;'>Database connection failed: " . $conn->connect_error . "</div>";
        } else {
            echo "<div style='color: green;'>✓ Database connected</div>";
            
            // Check if admin exists
            $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            echo "<div>Found users: " . $result->num_rows . "</div>";
            
            if ($result->num_rows == 1) {
                $admin = $result->fetch_assoc();
                
                echo "<div style='background: #e6ffe6; padding: 10px;'>";
                echo "User found:<br>";
                echo "ID: " . $admin['id'] . "<br>";
                echo "Username: " . $admin['username'] . "<br>";
                echo "Stored Hash: " . substr($admin['password'], 0, 30) . "...<br>";
                echo "</div>";
                
                // Test password
                echo "<div>Testing password...</div>";
                
                if (password_verify($password, $admin['password'])) {
                    echo "<div style='color: green; font-size: 20px;'>✓ PASSWORD VERIFIED!</div>";
                    
                    // Set session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    
                    echo "<div style='color: green;'>Session created. Redirecting...</div>";
                    
                    // Redirect to simple dashboard
                    header("refresh:2;url=simple_dashboard.php");
                    exit();
                    
                } else {
                    echo "<div style='color: red; font-size: 20px;'>✗ PASSWORD FAILED!</div>";
                    
                    // Show what the hash should be
                    $correct_hash = password_hash($password, PASSWORD_DEFAULT);
                    echo "<div>Correct hash should be: $correct_hash</div>";
                    
                    // Update the password
                    $update_stmt = $conn->prepare("UPDATE admin SET password = ? WHERE username = ?");
                    $update_stmt->bind_param("ss", $correct_hash, $username);
                    if ($update_stmt->execute()) {
                        echo "<div style='color: orange;'>Password updated in database. Try again.</div>";
                    }
                }
                
            } else {
                echo "<div style='color: red;'>User not found</div>";
                
                // Create the user
                $hash = password_hash('admin123', PASSWORD_DEFAULT);
                $create_stmt = $conn->prepare("INSERT INTO admin (username, password, email) VALUES (?, ?, ?)");
                $email = 'admin@pharmacy.com';
                $create_stmt->bind_param("sss", $username, $hash, $email);
                
                if ($create_stmt->execute()) {
                    echo "<div style='color: green;'>User created! Try logging in again.</div>";
                }
            }
            
            $stmt->close();
            $conn->close();
        }
        
    } catch (Exception $e) {
        echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Standalone Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .login-form { 
            max-width: 400px; 
            margin: 20px 0; 
            padding: 20px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
            background: #f9f9f9;
        }
        input { 
            width: 100%; 
            padding: 10px; 
            margin: 5px 0; 
            border: 1px solid #ccc; 
            border-radius: 3px;
            box-sizing: border-box;
        }
        button { 
            width: 100%; 
            padding: 10px; 
            background: #007bff; 
            color: white; 
            border: none; 
            border-radius: 3px;
            cursor: pointer;
        }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🔧 Standalone Login Test</h1>
    
    <div class="login-form">
        <form method="POST">
            <h3>Test Login</h3>
            
            <label>Username:</label>
            <input type="text" name="username" value="admin" required>
            
            <label>Password:</label>
            <input type="password" name="password" value="admin123" required>
            
            <button type="submit">🚀 Test Login</button>
        </form>
    </div>
    
    <div style="background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin-top: 20px;">
        <h4>🔍 What This Does:</h4>
        <ul>
            <li>Shows exactly what's happening during login</li>
            <li>Creates user if not exists</li>
            <li>Fixes password hash automatically</li>
            <li>Shows password verification step-by-step</li>
            <li>Redirects to simple dashboard if successful</li>
        </ul>
    </div>
    
    <div style="margin-top: 20px;">
        <a href="simple_dashboard.php">Go to Simple Dashboard</a> | 
        <a href="login.php">Go to Normal Login</a>
    </div>
</body>
</html>
