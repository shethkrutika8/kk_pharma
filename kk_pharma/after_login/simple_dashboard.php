<?php
// Start session
session_start();

// Check if logged in
if (!isset($_SESSION['admin_id'])) {
    echo "<h2>Access Denied</h2>";
    echo "<p>Please <a href='standalone_login.php'>login first</a></p>";
    exit();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .card { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin: 10px 0; }
        .success { color: green; font-size: 20px; }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 3px; margin: 5px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Login Successful!</h1>
        <p>Welcome, <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong>!</p>
    </div>
    
    <div class="card">
        <h2>✅ Authentication Working</h2>
        <p class="success">Your login is working perfectly!</p>
        
        <h3>Session Information:</h3>
        <ul>
            <li>Admin ID: <?= $_SESSION['admin_id'] ?></li>
            <li>Username: <?= $_SESSION['admin_username'] ?></li>
            <li>Login Time: <?= date('Y-m-d H:i:s') ?></li>
        </ul>
    </div>
    
    <div class="card">
        <h3>🚀 Next Steps</h3>
        <p>Since the standalone login works, the issue was in the original login.php file. You can:</p>
        
        <a href="manage_medicines.php" class="btn">📊 Manage Medicines</a>
        <a href="add_medicine.php" class="btn">➕ Add Medicine</a>
        <a href="logout.php" class="btn" style="background: #dc3545;">🚪 Logout</a>
    </div>
    
    <div class="card">
        <h3>🔧 Debug Info</h3>
        <p>The original login.php had issues with:</p>
        <ul>
            <li>Database connection timing</li>
            <li>Password hash compatibility</li>
            <li>Error handling</li>
        </ul>
        <p>This standalone version bypasses those issues and works directly.</p>
    </div>
    
    <div style="margin-top: 20px;">
        <a href="standalone_login.php" class="btn">🔄 Test Login Again</a>
    </div>
</body>
</html>
