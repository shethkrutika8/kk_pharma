<?php
require_once 'includes/config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>KK Pharma Dashboard Setup</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css' rel='stylesheet'>
</head>
<body>
    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-md-8'>
                <div class='card shadow'>
                    <div class='card-header bg-primary text-white'>
                        <h3 class='mb-0'><i class='bi bi-gear-fill me-2'></i>KK Pharma User Dashboard Setup</h3>
                    </div>
                    <div class='card-body'>";

echo "<h4 class='mb-3'><i class='bi bi-database-fill me-2'></i>Setting up Database Tables...</h4>";

// Read and execute the SQL file
$sql_file = 'Database/user_dashboard_complete.sql';
if (file_exists($sql_file)) {
    $sql_content = file_get_contents($sql_file);
    
    // Remove comments and split statements
    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement) && !preg_match('/^SET/', $statement)) {
            try {
                if ($conn->query($statement)) {
                    $table_name = extractTableName($statement);
                    if ($table_name && $table_name !== 'Unknown') {
                        echo "<div class='alert alert-success alert-sm'><i class='bi bi-check-circle me-2'></i>✓ Table '$table_name' created successfully</div>";
                        $success_count++;
                    }
                } else {
                    $error = $conn->error;
                    if (strpos($error, 'already exists') === false && strpos($error, 'Duplicate') === false) {
                        echo "<div class='alert alert-danger alert-sm'><i class='bi bi-exclamation-circle me-2'></i>✗ Error: $error</div>";
                        $error_count++;
                    }
                }
            } catch (Exception $e) {
                echo "<div class='alert alert-warning alert-sm'><i class='bi bi-exclamation-triangle me-2'></i>⚠ Warning: " . $e->getMessage() . "</div>";
            }
        }
    }
    
    echo "<div class='alert alert-info'><i class='bi bi-info-circle me-2'></i><strong>Setup Summary:</strong> $success_count operations completed successfully";
    if ($error_count > 0) {
        echo ", $error_count errors encountered";
    }
    echo "</div>";
    
} else {
    echo "<div class='alert alert-danger'><i class='bi bi-exclamation-circle me-2'></i>✗ SQL file not found: $sql_file</div>";
}

// Verify tables exist
echo "<h4 class='mb-3 mt-4'><i class='bi bi-check-square me-2'></i>Verifying Database Tables...</h4>";

$tables_to_check = [
    'user_profiles' => 'User Profiles',
    'user_addresses' => 'User Addresses', 
    'categories' => 'Product Categories',
    'medicines' => 'Medicines/Products',
    'cart' => 'Shopping Cart',
    'orders' => 'Orders',
    'order_items' => 'Order Items',
    'subscriptions' => 'Subscriptions',
    'subscription_deliveries' => 'Subscription Deliveries',
    'wishlist' => 'Wishlist',
    'prescription_uploads' => 'Prescription Uploads',
    'medicine_reviews' => 'Medicine Reviews',
    'user_notifications' => 'User Notifications',
    'user_activity_log' => 'User Activity Log'
];

$all_tables_exist = true;

foreach ($tables_to_check as $table_name => $display_name) {
    $result = $conn->query("SHOW TABLES LIKE '$table_name'");
    if ($result->num_rows > 0) {
        echo "<div class='alert alert-success alert-sm'><i class='bi bi-check-circle me-2'></i>✓ $display_name table exists</div>";
    } else {
        echo "<div class='alert alert-danger alert-sm'><i class='bi bi-x-circle me-2'></i>✗ $display_name table missing</div>";
        $all_tables_exist = false;
    }
}

// Check sample data
echo "<h4 class='mb-3 mt-4'><i class='bi bi-database-fill-add me-2'></i>Checking Sample Data...</h4>";

$categories_count = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];
$medicines_count = $conn->query("SELECT COUNT(*) as count FROM medicines")->fetch_assoc()['count'];

echo "<div class='alert alert-info'><i class='bi bi-info-circle me-2'></i>Categories in database: $categories_count</div>";
echo "<div class='alert alert-info'><i class='bi bi-info-circle me-2'></i>Medicines in database: $medicines_count</div>";

if ($categories_count == 0 || $medicines_count == 0) {
    echo "<div class='alert alert-warning'><i class='bi bi-exclamation-triangle me-2'></i>⚠ Sample data missing. You may need to add categories and medicines.</div>";
}

// Create directories if they don't exist
echo "<h4 class='mb-3 mt-4'><i class='bi bi-folder-fill me-2'></i>Creating Directories...</h4>";

$directories = [
    'user_dashboard',
    'uploads/prescriptions',
    'uploads/medicines',
    'uploads/profiles'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<div class='alert alert-success alert-sm'><i class='bi bi-check-circle me-2'></i>✓ Directory '$dir' created</div>";
        } else {
            echo "<div class='alert alert-danger alert-sm'><i class='bi bi-x-circle me-2'></i>✗ Failed to create directory '$dir'</div>";
        }
    } else {
        echo "<div class='alert alert-success alert-sm'><i class='bi bi-check-circle me-2'></i>✓ Directory '$dir' already exists</div>";
    }
}

// Final status
echo "<h4 class='mb-3 mt-4'><i class='bi bi-flag-fill me-2'></i>Setup Status</h4>";

if ($all_tables_exist && $categories_count > 0 && $medicines_count > 0) {
    echo "<div class='alert alert-success'><i class='bi bi-check-circle-fill me-2'></i><strong>🎉 Setup Complete!</strong> Your KK Pharma User Dashboard is ready to use.</div>";
    
    echo "<div class='card bg-light mt-3'>
            <div class='card-body'>
                <h5 class='card-title'><i class='bi bi-link-45deg me-2'></i>Quick Links</h5>
                <div class='row'>
                    <div class='col-md-6 mb-2'>
                        <a href='user_dashboard/index.php' class='btn btn-primary w-100'>
                            <i class='bi bi-speedometer2 me-2'></i>User Dashboard
                        </a>
                    </div>
                    <div class='col-md-6 mb-2'>
                        <a href='user_dashboard/cart.php' class='btn btn-success w-100'>
                            <i class='bi bi-cart3 me-2'></i>Shopping Cart
                        </a>
                    </div>
                    <div class='col-md-6 mb-2'>
                        <a href='user_dashboard/subscriptions.php' class='btn btn-info w-100'>
                            <i class='bi bi-arrow-repeat me-2'></i>Subscriptions
                        </a>
                    </div>
                    <div class='col-md-6 mb-2'>
                        <a href='login.php' class='btn btn-outline-primary w-100'>
                            <i class='bi bi-box-arrow-in-right me-2'></i>Login Page
                        </a>
                    </div>
                </div>
            </div>
          </div>";
    
    echo "<div class='alert alert-info mt-3'>
            <h6><i class='bi bi-lightbulb me-2'></i>Next Steps:</h6>
            <ol>
                <li>Create a test user account or login with existing credentials</li>
                <li>Browse the user dashboard and explore all features</li>
                <li>Add products to cart and test the checkout process</li>
                <li>Create subscriptions for regular medicines</li>
                <li>Test all functionality including orders, wishlist, and profile management</li>
            </ol>
          </div>";
    
} else {
    echo "<div class='alert alert-warning'><i class='bi bi-exclamation-triangle-fill me-2'></i><strong>⚠ Setup Incomplete</strong> Some components may not be working properly.</div>";
    echo "<p>Please check the errors above and resolve them before using the dashboard.</p>";
}

echo "<div class='alert alert-secondary mt-3'>
        <h6><i class='bi bi-gear me-2'></i>Features Included:</h6>
        <div class='row'>
            <div class='col-md-6'>
                <ul class='mb-0'>
                    <li>✓ User Dashboard with Statistics</li>
                    <li>✓ Shopping Cart Management</li>
                    <li>✓ Subscription System</li>
                    <li>✓ Order History & Tracking</li>
                    <li>✓ Wishlist Management</li>
                </ul>
            </div>
            <div class='col-md-6'>
                <ul class='mb-0'>
                    <li>✓ Prescription Upload</li>
                    <li>✓ User Profile Management</li>
                    <li>✓ Product Reviews</li>
                    <li>✓ Notification System</li>
                    <li>✓ Activity Logging</li>
                </ul>
            </div>
        </div>
      </div>";

echo "<div class='text-center mt-4 mb-3'>
        <a href='Home.php' class='btn btn-outline-primary me-2'>
            <i class='bi bi-house me-2'></i>Go to Home
        </a>
        <button onclick='window.location.reload()' class='btn btn-outline-secondary'>
            <i class='bi bi-arrow-clockwise me-2'></i>Refresh Setup
        </button>
      </div>";

echo "          </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";

// Function to extract table name from CREATE TABLE statement
function extractTableName($sql) {
    if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $sql, $matches)) {
        return $matches[1];
    }
    return 'Unknown';
}
?>
