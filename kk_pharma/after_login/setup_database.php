<?php
// Database setup script
$host = 'localhost';
$user = 'root';
$pass = '';

// Connect without database name first
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to MySQL server successfully.<br>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS pharmacy_db";
if ($conn->query($sql) === TRUE) {
    echo "Database 'pharmacy_db' created successfully or already exists.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Switch to the pharmacy_db database
$conn->select_db('pharmacy_db');

// Read and execute the SQL file
$sql_file = __DIR__ . '/database.sql';
if (file_exists($sql_file)) {
    $sql_content = file_get_contents($sql_file);
    
    // Split SQL statements by semicolon
    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            if ($conn->query($statement) === TRUE) {
                echo "✓ Executed: " . substr($statement, 0, 50) . "..." . "<br>";
            } else {
                echo "✗ Error: " . $conn->error . "<br>";
                echo "Statement: " . $statement . "<br><br>";
            }
        }
    }
    
    echo "<br><strong>Database setup completed!</strong><br>";
    echo '<a href="login.php">Go to Admin Login</a>';
} else {
    echo "Error: database.sql file not found.";
}

$conn->close();
?>
