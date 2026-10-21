<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Add Medicine Page</h2>";

// Step 1: Test config file
echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0;'>";
echo "<strong>Step 1: Testing config.php...</strong><br>";

try {
    require_once 'config.php';
    echo "✓ Config file loaded successfully<br>";
    
    // Test database connection
    if (isset($conn) && $conn->ping()) {
        echo "✓ Database connection working<br>";
    } else {
        echo "✗ Database connection failed<br>";
    }
    
    // Test session
    if (session_status() == PHP_SESSION_ACTIVE) {
        echo "✓ Session active<br>";
    } else {
        echo "✗ Session not active<br>";
    }
    
    // Test login functions
    if (function_exists('isAdminLoggedIn')) {
        echo "✓ isAdminLoggedIn function exists<br>";
        
        $is_logged_in = isAdminLoggedIn();
        echo "Logged in status: " . ($is_logged_in ? "YES" : "NO") . "<br>";
        
        if ($is_logged_in) {
            echo "Admin ID: " . $_SESSION['admin_id'] . "<br>";
            echo "Admin Username: " . $_SESSION['admin_username'] . "<br>";
        }
    } else {
        echo "✗ isAdminLoggedIn function missing<br>";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

echo "</div>";

// Step 2: Test database tables
echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0;'>";
echo "<strong>Step 2: Testing database tables...</strong><br>";

try {
    $result = $conn->query("SHOW TABLES LIKE 'medicines'");
    if ($result->num_rows > 0) {
        echo "✓ Medicines table exists<br>";
        
        $count = $conn->query("SELECT COUNT(*) as count FROM medicines")->fetch_assoc()['count'];
        echo "Medicines in database: $count<br>";
    } else {
        echo "✗ Medicines table missing<br>";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

echo "</div>";

// Step 3: Test form processing
echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0;'>";
echo "<strong>Step 3: Testing form processing...</strong><br>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "Form submitted<br>";
    
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    
    echo "Name: '$name'<br>";
    echo "Price: '$price'<br>";
    
    // Test sanitizeInput function
    if (function_exists('sanitizeInput')) {
        echo "✓ sanitizeInput function exists<br>";
    } else {
        echo "✗ sanitizeInput function missing<br>";
    }
} else {
    echo "No form submitted<br>";
}

echo "</div>";

// Step 4: Show actual error from add_medicine.php
echo "<div style='background: #ffe6e6; padding: 10px; margin: 10px 0;'>";
echo "<strong>Step 4: Loading actual add_medicine.php...</strong><br>";

// Capture any errors from including the file
ob_start();
include 'add_medicine.php';
$output = ob_get_clean();

if (empty($output)) {
    echo "✗ add_medicine.php produced no output<br>";
} else {
    echo "✓ add_medicine.php loaded successfully<br>";
}

echo "</div>";

echo "<br><div style='padding: 10px; background: #e6ffe6; border: 1px solid green;'>";
echo "<strong>If you see errors above, here's what to do:</strong><br>";
echo "1. Copy the error messages<br>";
echo "2. Tell me exactly what they say<br>";
echo "3. I'll fix the specific issue<br><br>";
echo "<a href='add_medicine.php'>Try Add Medicine Again</a> | ";
echo "<a href='simple_dashboard.php'>Go to Dashboard</a>";
echo "</div>";
?>
