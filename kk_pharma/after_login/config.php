<?php
// Error reporting - enable for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'pharmacy_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Function to create database and tables if they don't exist
function ensureDatabaseExists() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return false;
    }
    
    // Create database if it doesn't exist
    $conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $conn->select_db(DB_NAME);
    
    // Create register table if it doesn't exist (for users)
    $conn->query("CREATE TABLE IF NOT EXISTS register (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add missing columns to register table if they don't exist
    $register_columns = [
        'name' => "VARCHAR(100) NOT NULL DEFAULT ''",
        'phone' => "VARCHAR(20)",
        'address' => "TEXT"
    ];
    
    foreach ($register_columns as $column => $definition) {
        $result = $conn->query("SHOW COLUMNS FROM register LIKE '$column'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE register ADD COLUMN $column $definition");
        }
    }
    
    // Create user_profiles table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS user_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL UNIQUE,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        date_of_birth DATE,
        gender ENUM('male', 'female', 'other'),
        profile_image VARCHAR(255),
        email_verified BOOLEAN DEFAULT FALSE,
        phone_verified BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES register(id) ON DELETE CASCADE
    )");
    
    // Create user_addresses table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS user_addresses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        address_type ENUM('home', 'work', 'other') DEFAULT 'home',
        address_line1 VARCHAR(255) NOT NULL,
        address_line2 VARCHAR(255),
        city VARCHAR(100) NOT NULL,
        state VARCHAR(100) NOT NULL,
        postal_code VARCHAR(20) NOT NULL,
        country VARCHAR(100) DEFAULT 'India',
        is_default BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES register(id) ON DELETE CASCADE
    )");
    
    // Create admin table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create categories table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add missing columns to categories table if they don't exist
    $categories_columns_to_add = [
        'status' => "ENUM('active', 'inactive') DEFAULT 'active'",
        'sort_order' => "INT DEFAULT 0",
        'icon' => "VARCHAR(50) DEFAULT 'grid'"
    ];
    
    foreach ($categories_columns_to_add as $column => $definition) {
        $result = $conn->query("SHOW COLUMNS FROM categories LIKE '$column'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE categories ADD COLUMN $column $definition");
        }
    }
    
    // Create medicines table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS medicines (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        stock_quantity INT DEFAULT 0,
        manufacturer VARCHAR(200),
        expiry_date DATE,
        image_url VARCHAR(500),
        prescription_required BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add missing columns to medicines table if they don't exist
    $columns_to_add = [
        'category_id' => "INT",
        'discount_price' => "DECIMAL(10,2)",
        'rating' => "DECIMAL(3,2) DEFAULT 0.00",
        'review_count' => "INT DEFAULT 0",
        'is_featured' => "BOOLEAN DEFAULT FALSE",
        'status' => "ENUM('active', 'inactive') DEFAULT 'active'",
        'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ];
    
    foreach ($columns_to_add as $column => $definition) {
        $result = $conn->query("SHOW COLUMNS FROM medicines LIKE '$column'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE medicines ADD COLUMN $column $definition");
        }
    }
    
    // Add foreign key for category_id if it doesn't exist
    $result = $conn->query("SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = 'medicines' AND CONSTRAINT_NAME = 'medicines_ibfk_1'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE medicines ADD FOREIGN KEY (category_id) REFERENCES categories(id)");
    }
    
    // Create cart table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        medicine_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES register(id) ON DELETE CASCADE,
        FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE,
        UNIQUE KEY unique_cart_item (user_id, medicine_id)
    )");
    
    // Create orders table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES register(id)
    )");
    
    // Create order_items table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        medicine_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (medicine_id) REFERENCES medicines(id)
    )");
    
    // Create wishlist table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        medicine_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES register(id) ON DELETE CASCADE,
        FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE,
        UNIQUE KEY unique_wishlist_item (user_id, medicine_id)
    )");
    
    // Create subscriptions table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        medicine_id INT NOT NULL,
        subscription_type ENUM('weekly', 'monthly', 'quarterly', 'yearly') NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        price DECIMAL(10,2) NOT NULL,
        status ENUM('active', 'paused', 'cancelled', 'expired') DEFAULT 'active',
        start_date DATE NOT NULL,
        end_date DATE,
        next_delivery_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES register(id) ON DELETE CASCADE,
        FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
    )");
    
    // Insert default admin if not exists
    $result = $conn->query("SELECT id FROM admin WHERE username = 'admin' OR email = 'admin@pharmacy.com'");
    if ($result->num_rows == 0) {
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO admin (username, password, email) VALUES 
            ('admin', '$hashed_password', 'admin@pharmacy.com')");
    }
    
    // Insert sample categories if table is empty
    $result = $conn->query("SELECT id FROM categories LIMIT 1");
    if ($result->num_rows == 0) {
        $conn->query("INSERT INTO categories (name, description, status, sort_order, icon) VALUES
            ('Pain Relief', 'Pain relief medications and analgesics', 'active', 1, 'capsule'),
            ('Antibiotics', 'Antibiotic medications for bacterial infections', 'active', 2, 'shield-plus'),
            ('Vitamins & Supplements', 'Dietary supplements and vitamins', 'active', 3, 'heart-pulse'),
            ('Cold & Flu', 'Medications for cold and flu symptoms', 'active', 4, 'thermometer')");
    }
    
    // Insert sample medicines if table is empty
    $result = $conn->query("SELECT id FROM medicines LIMIT 1");
    if ($result->num_rows == 0) {
        $conn->query("INSERT INTO medicines (name, description, category_id, price, discount_price, stock_quantity, manufacturer, expiry_date, prescription_required, rating, review_count, is_featured, image_url) VALUES
            ('Paracetamol 500mg', 'Paracetamol tablets for fever and pain relief', 1, 25.00, 20.00, 100, 'Cipla Ltd', '2025-12-31', FALSE, 4.5, 128, TRUE, '../images/medicines/paracetamol.jpg'),
            ('Azithromycin 250mg', 'Antibiotic for bacterial infections', 2, 85.00, 75.00, 50, 'Pfizer Ltd', '2025-08-15', TRUE, 4.6, 67, FALSE, '../images/medicines/azithromycin.jpg'),
            ('Vitamin C Tablets', 'Vitamin C supplement for immunity', 3, 210.00, 180.00, 75, 'Dabur India', '2026-03-20', FALSE, 4.4, 156, TRUE, '../images/medicines/vitamin-c.jpg'),
            ('Cough Syrup', 'Relieves cough and throat irritation', 4, 120.00, 100.00, 60, 'Himalaya', '2025-10-10', FALSE, 4.2, 94, FALSE, '../images/medicines/cough-syrup.jpg')");
    }
    
    $conn->close();
    return true;
}

// Ensure database exists before connecting
if (!ensureDatabaseExists()) {
    die("Failed to initialize database. Please check your database configuration.");
}

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection failed. Please check your database configuration.");
}

// Start session
session_start();

// Admin authentication functions
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function displayAlert($message, $type = 'success') {
    return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

// Database connection function
function getDBConnection() {
    global $conn;
    return $conn;
}

// Format price function
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

// Format date function
function formatDate($date, $format = 'Y-m-d H:i:s') {
    $datetime = new DateTime($date);
    return $datetime->format($format);
}

// Get medicine by ID function
function getMedicineById($medicine_id) {
    $conn = getDBConnection();
    
    // Check if status column exists
    $result = $conn->query("SHOW COLUMNS FROM medicines LIKE 'status'");
    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("SELECT * FROM medicines WHERE id = ? AND (status = 'active' OR status IS NULL)");
    } else {
        $stmt = $conn->prepare("SELECT * FROM medicines WHERE id = ?");
    }
    $stmt->bind_param("i", $medicine_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

// Cart functions
function addToCart($medicine_id, $quantity = 1) {
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'] ?? 0;
    
    if (!$user_id) {
        error_log("addToCart: No user ID in session");
        return false;
    }
    
    $medicine = getMedicineById($medicine_id);
    if (!$medicine) {
        error_log("addToCart: Medicine not found with ID: $medicine_id");
        return false;
    }
    
    if (!isset($medicine['stock_quantity']) || $medicine['stock_quantity'] < $quantity) {
        error_log("addToCart: Insufficient stock. Available: " . ($medicine['stock_quantity'] ?? 'null') . ", Requested: $quantity");
        return false;
    }
    
    // Check if item already in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND medicine_id = ?");
    $stmt->bind_param("ii", $user_id, $medicine_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    
    if ($existing) {
        // Update quantity
        $new_quantity = $existing['quantity'] + $quantity;
        if ($medicine['stock_quantity'] < $new_quantity) {
            return false;
        }
        
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_quantity, $existing['id']);
    } else {
        // Add new item
        $stmt = $conn->prepare("INSERT INTO cart (user_id, medicine_id, quantity, price) VALUES (?, ?, ?, ?)");
        $price = $medicine['discount_price'] ?? $medicine['price'];
        $stmt->bind_param("iiid", $user_id, $medicine_id, $quantity, $price);
    }
    
    return $stmt->execute();
}

function getCartItems() {
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT c.*, m.name, m.image_url, m.stock_quantity FROM cart c JOIN medicines m ON c.medicine_id = m.id WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $items = [];
    $result = $stmt->get_result();
    while ($item = $result->fetch_assoc()) {
        $items[] = $item;
    }
    
    return $items;
}

function getCartTotal() {
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT SUM(c.quantity * c.price) as total FROM cart c WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

function getCartCount() {
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] ?? 0;
}
?>
