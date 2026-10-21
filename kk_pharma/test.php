<?php
// Simple test to check what's causing blank pages
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Test page started...<br>";

// Test database connection
$con = mysqli_connect("localhost", "root", "");

if ($con) {
    echo "Database connection successful.<br>";
    
    // Test database selection
    if (mysqli_select_db($con, "kk_pharma")) {
        echo "Database 'kk_pharma' selected successfully.<br>";
    } else {
        echo "Error selecting database: " . mysqli_error($con) . "<br>";
    }
    
    // Test session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        echo "Session started successfully.<br>";
    } else {
        echo "Session already active.<br>";
    }
    
    echo "All basic tests passed!<br>";
    
} else {
    echo "Database connection failed: " . mysqli_connect_error() . "<br>";
}

echo "Test completed.";
?>
