-- Database: pharmacy_db
CREATE DATABASE IF NOT EXISTS pharmacy_db;
USE pharmacy_db;

-- Admin table
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO admin (username, password, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@pharmacy.com');

-- Medicines table
CREATE TABLE IF NOT EXISTS medicines (
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
);

-- Insert sample medicines
INSERT INTO medicines (name, description, category, price, old_price, stock_quantity, manufacturer, expiry_date, prescription_required) VALUES
('Paracetamol 500mg', 'Paracetamol tablets for fever and pain relief', 'Pain Relief', 25.00, 30.00, 100, 'Cipla Ltd', '2025-12-31', FALSE),
('Azithromycin 250mg', 'Antibiotic for bacterial infections', 'Antibiotics', 85.00, 99.00, 50, 'Pfizer Ltd', '2025-08-15', TRUE),
('Vitamin C Tablets', 'Vitamin C supplement for immunity', 'Vitamins', 210.00, 250.00, 75, 'Dabur India', '2026-03-20', FALSE),
('Cough Syrup', 'Relieves cough and throat irritation', 'Cough & Cold', 120.00, 150.00, 60, 'Himalaya', '2025-10-10', FALSE);
