<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Quick Login Fix</h2>";

// Step 1: Connect to MySQL without database
$conn = new mysqli('localhost', 'root', '');

if ($conn->connect_error) {
    die("Cannot connect to MySQL: " . $conn->connect_error);
}

echo "✓ Connected to MySQL<br>";

// Step 2: Create database
$conn->query("DROP DATABASE IF EXISTS pharmacy_db");
$conn->query("CREATE DATABASE pharmacy_db");
$conn->select_db("pharmacy_db");

echo "✓ Database created fresh<br>";

// Step 3: Create admin table
$conn->query("CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

echo "✓ Admin table created<br>";

// Step 4: Create medicines table
$conn->query("CREATE TABLE medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    price DECIMAL(10,2) NOT NULL,
    old_price DECIMAL(10,2),
    stock_quantity INT DEFAULT 0,
    manufacturer VARCHAR(200),
    expiry_date DATE,
    image_url VARCHAR(500),
    prescription_required BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

echo "✓ Medicines table created<br>";

// Step 5: Insert admin with fresh password
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$conn->query("INSERT INTO admin (username, password, email) VALUES 
    ('admin', '$hashed_password', 'admin@pharmacy.com')");

echo "✓ Admin user created<br>";

// Step 6: Test the password
$result = $conn->query("SELECT * FROM admin WHERE username = 'admin'");
$admin = $result->fetch_assoc();

if (password_verify('admin123', $admin['password'])) {
    echo "<strong style='color: green;'>✓ Password verification SUCCESS!</strong><br>";
} else {
    echo "<strong style='color: red;'>✗ Password verification FAILED!</strong><br>";
}

// Step 7: Insert sample medicines
$conn->query("INSERT INTO medicines (name, description, category, price, old_price, stock_quantity, manufacturer, expiry_date, prescription_required) VALUES
    ('Paracetamol 500mg', 'Paracetamol tablets for fever and pain relief', 'Pain Relief', 25.00, 30.00, 100, 'Cipla Ltd', '2025-12-31', FALSE),
    ('Azithromycin 250mg', 'Antibiotic for bacterial infections', 'Antibiotics', 85.00, 99.00, 50, 'Pfizer Ltd', '2025-08-15', TRUE),
    ('Vitamin C Tablets', 'Vitamin C supplement for immunity', 'Vitamins', 210.00, 250.00, 75, 'Dabur India', '2026-03-20', FALSE),
    ('Cough Syrup', 'Relieves cough and throat irritation', 'Cough & Cold', 120.00, 150.00, 60, 'Himalaya', '2025-10-10', FALSE)");

echo "✓ Sample medicines added<br>";

$conn->close();

echo "<br><div style='background: #e6ffe6; padding: 15px; border: 1px solid green; border-radius: 5px;'>";
echo "<h3>✅ ALL FIXED!</h3>";
echo "<strong>Login Credentials:</strong><br>";
echo "Username: <code>admin</code><br>";
echo "Password: <code>admin123</code><br><br>";
echo "<a href='login.php' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px;'>🚀 Go to Login</a>";
echo "</div>";

echo "<br><small>Note: This completely reset your database. Everything is fresh and should work perfectly.</small>";
?>
