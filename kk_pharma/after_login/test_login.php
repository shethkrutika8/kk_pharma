<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    echo "<h3>Debugging Login Attempt</h3>";
    echo "Username entered: <strong>$username</strong><br>";
    echo "Password entered: <strong>$password</strong><br><br>";
    
    $sql = "SELECT id, username, password FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "SQL Query executed: $sql<br>";
    echo "Number of results found: " . $result->num_rows . "<br><br>";
    
    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        echo "User found in database:<br>";
        echo "ID: " . $admin['id'] . "<br>";
        echo "Username: " . $admin['username'] . "<br>";
        echo "Stored Password Hash: " . $admin['password'] . "<br><br>";
        
        echo "Testing password verification...<br>";
        
        if (password_verify($password, $admin['password'])) {
            echo "<strong style='color: green;'>✓ Password verification SUCCESS!</strong><br>";
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            echo "Session created. Redirecting to dashboard...<br>";
            header("refresh:2;url=admin_dashboard.php");
        } else {
            echo "<strong style='color: red;'>✗ Password verification FAILED!</strong><br>";
            echo "Trying manual hash comparison...<br>";
            
            // Try creating a new hash and comparing
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            echo "New hash would be: $new_hash<br>";
            
            if ($new_hash === $admin['password']) {
                echo "Hashes match exactly<br>";
            } else {
                echo "Hashes don't match<br>";
            }
        }
    } else {
        echo "<strong style='color: red;'>✗ User not found in database</strong><br>";
    }
    
    echo "<br><hr><br>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Login</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .form-group { margin: 10px 0; }
        input { padding: 8px; width: 200px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Test Login Page</h2>
    
    <form method="POST">
        <div class="form-group">
            <label>Username:</label><br>
            <input type="text" name="username" value="admin" required>
        </div>
        <div class="form-group">
            <label>Password:</label><br>
            <input type="password" name="password" value="admin123" required>
        </div>
        <div class="form-group">
            <button type="submit">Test Login</button>
        </div>
    </form>
    
    <br>
    <a href="debug_login.php">Run Debug Script</a> | 
    <a href="login.php">Go to Normal Login</a>
</body>
</html>
