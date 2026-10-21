<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting page...<br>";

try {
    include_once("Database/db_config.php");
    echo "Database config included successfully.<br>";
} catch (Exception $e) {
    echo "Error including db_config: " . $e->getMessage() . "<br>";
}

echo "Page loaded successfully!";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Page</title>
</head>
<body>
    <h1>Test Page</h1>
    <p>If you can see this, the basic PHP is working.</p>
</body>
</html>
