-- ========================================
-- KK Pharma User Dashboard Complete Database
-- ========================================

-- Drop existing tables if they exist (for fresh setup)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS subscription_deliveries;
DROP TABLE IF EXISTS subscriptions;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS wishlist;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS prescription_uploads;
DROP TABLE IF EXISTS medicines;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS user_addresses;
DROP TABLE IF EXISTS user_profiles;
SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- 1. User Profiles Table (Extended user info)
-- ========================================
CREATE TABLE user_profiles (
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
);

-- ========================================
-- 2. User Addresses Table
-- ========================================
CREATE TABLE user_addresses (
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
);

-- ========================================
-- 3. Categories Table
-- ========================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image_url VARCHAR(255),
    icon VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- 4. Medicines Table (Enhanced)
-- ========================================
CREATE TABLE medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    generic_name VARCHAR(200),
    description TEXT,
    category_id INT,
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2),
    stock_quantity INT DEFAULT 0,
    min_stock_level INT DEFAULT 10,
    manufacturer VARCHAR(100),
    brand VARCHAR(100),
    expiry_date DATE,
    prescription_required BOOLEAN DEFAULT FALSE,
    image_url VARCHAR(255),
    images JSON, -- Multiple images
    sku VARCHAR(100) UNIQUE,
    barcode VARCHAR(100),
    weight DECIMAL(8,2),
    dimensions VARCHAR(50),
    storage_conditions TEXT,
    side_effects TEXT,
    dosage_info TEXT,
    contraindications TEXT,
    rating DECIMAL(3,2) DEFAULT 0.00,
    review_count INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_digital BOOLEAN DEFAULT FALSE,
    tags JSON,
    status ENUM('active', 'inactive', 'discontinued') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_name (name),
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    INDEX idx_price (price),
    FULLTEXT idx_search (name, generic_name, description, brand, manufacturer)
);

-- ========================================
-- 5. Shopping Cart Table
-- ========================================
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL, -- Price at time of adding to cart
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES register(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, medicine_id),
    INDEX idx_user (user_id)
);

-- ========================================
-- 6. Orders Table (Enhanced)
-- ========================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    delivery_charge DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'cancelled', 'returned') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded', 'partially_refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_transaction_id VARCHAR(100),
    shipping_address JSON,
    billing_address JSON,
    phone VARCHAR(20),
    email VARCHAR(100),
    order_notes TEXT,
    prescription_required BOOLEAN DEFAULT FALSE,
    prescription_verified BOOLEAN DEFAULT FALSE,
    estimated_delivery DATE,
    actual_delivery DATE,
    tracking_number VARCHAR(100),
    courier_service VARCHAR(100),
    cancel_reason TEXT,
    return_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES register(id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_order_number (order_number),
    INDEX idx_created_at (created_at)
);

-- ========================================
-- 7. Order Items Table
-- ========================================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL, -- Price at time of order
    discount_price DECIMAL(10,2),
    subtotal DECIMAL(10,2) NOT NULL,
    medicine_name VARCHAR(200), -- Store name at time of order
    medicine_image VARCHAR(255), -- Store image at time of order
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id),
    INDEX idx_order (order_id),
    INDEX idx_medicine (medicine_id)
);

-- ========================================
-- 8. Subscriptions Table (Enhanced)
-- ========================================
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medicine_id INT NOT NULL,
    subscription_type ENUM('monthly', 'quarterly', 'yearly', 'weekly') NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL, -- Price at time of subscription
    discount_percentage DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('active', 'paused', 'cancelled', 'expired', 'completed') DEFAULT 'active',
    start_date DATE NOT NULL,
    end_date DATE,
    next_delivery_date DATE,
    delivery_day_of_month INT, -- For monthly subscriptions (1-31)
    delivery_frequency_weeks INT, -- For custom frequency
    delivery_address JSON,
    billing_address JSON,
    auto_renew BOOLEAN DEFAULT TRUE,
    payment_method VARCHAR(50),
    total_deliveries INT DEFAULT 0,
    remaining_deliveries INT,
    pause_reason TEXT,
    cancel_reason TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES register(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_next_delivery (next_delivery_date)
);

-- ========================================
-- 9. Subscription Deliveries Table
-- ========================================
CREATE TABLE subscription_deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    delivery_number INT NOT NULL,
    delivery_date DATE NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'failed', 'cancelled') DEFAULT 'pending',
    order_id INT, -- Link to order when delivery is processed
    tracking_number VARCHAR(100),
    courier_service VARCHAR(100),
    delivery_address JSON,
    estimated_delivery DATE,
    actual_delivery DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    INDEX idx_subscription (subscription_id),
    INDEX idx_status (status),
    INDEX idx_delivery_date (delivery_date)
);

-- ========================================
-- 10. Wishlist Table
-- ========================================
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medicine_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES register(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist_item (user_id, medicine_id),
    INDEX idx_user (user_id)
);

-- ========================================
-- 11. Prescription Uploads Table (Enhanced)
-- ========================================
CREATE TABLE prescription_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    prescription_type ENUM('new', 'refill') DEFAULT 'new',
    doctor_name VARCHAR(100),
    hospital_name VARCHAR(100),
    prescription_date DATE,
    expiry_date DATE,
    status ENUM('pending', 'verified', 'rejected', 'expired') DEFAULT 'pending',
    verified_by INT,
    verification_notes TEXT,
    reject_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES register(id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (verified_by) REFERENCES admins(id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_order (order_id)
);

-- ========================================
-- 12. Medicine Reviews Table
-- ========================================
CREATE TABLE medicine_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id INT NOT NULL,
    user_id INT NOT NULL,
    order_item_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    pros TEXT,
    cons TEXT,
    would_recommend BOOLEAN,
    is_verified_purchase BOOLEAN DEFAULT FALSE,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES register(id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id),
    UNIQUE KEY unique_review (user_id, medicine_id),
    INDEX idx_medicine (medicine_id),
    INDEX idx_rating (rating),
    INDEX idx_status (status)
);

-- ========================================
-- 13. User Notifications Table
-- ========================================
CREATE TABLE user_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error', 'order', 'subscription', 'promotion') DEFAULT 'info',
    related_id INT, -- Can be order_id, subscription_id, etc.
    related_type VARCHAR(50), -- 'order', 'subscription', 'medicine', etc.
    is_read BOOLEAN DEFAULT FALSE,
    action_url VARCHAR(255),
    action_text VARCHAR(100),
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES register(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at)
);

-- ========================================
-- 14. User Activity Log Table
-- ========================================
CREATE TABLE user_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(255),
    related_id INT,
    related_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES register(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_created_at (created_at)
);

-- ========================================
-- Insert Sample Categories
-- ========================================
INSERT INTO categories (name, description, icon, status, sort_order) VALUES
('Pain Relief', 'Pain relief medications and analgesics for various types of pain', 'bi-heart-pulse', 'active', 1),
('Antibiotics', 'Antibiotic medications for bacterial infections', 'bi-shield-check', 'active', 2),
('Vitamins & Supplements', 'Dietary supplements, vitamins and nutritional products', 'bi-star', 'active', 3),
('Cold & Flu', 'Medications for cold, flu and respiratory symptoms', 'bi-snow', 'active', 4),
('Digestive Health', 'Medications for digestive system and gastrointestinal health', 'bi-droplet', 'active', 5),
('Heart Health', 'Cardiovascular health medications and heart care products', 'bi-heart', 'active', 6),
('Diabetes Care', 'Diabetes management medications and monitoring devices', 'bi-activity', 'active', 7),
('Skin Care', 'Dermatological medications, creams and skin treatments', 'bi-patch', 'active', 8),
('Women\'s Health', 'Women\'s health medications and feminine care products', 'bi-person', 'active', 9),
('Men\'s Health', 'Men\'s health medications and supplements', 'bi-person-badge', 'active', 10),
('Baby Care', 'Baby care products, infant medications and pediatric supplies', 'bi-emoji-smile', 'active', 11),
('Eye Care', 'Eye drops, ointments and vision care products', 'bi-eye', 'active', 12),
('Allergy', 'Allergy medications and antihistamines', 'bi-flower1', 'active', 13),
('First Aid', 'First aid supplies, wound care and emergency medications', 'bi-kit', 'active', 14);

-- ========================================
-- Insert Sample Medicines with Enhanced Data
-- ========================================
INSERT INTO medicines (name, generic_name, description, category_id, price, discount_price, stock_quantity, manufacturer, brand, prescription_required, sku, barcode, rating, review_count, is_featured, tags, status) VALUES
('Paracetamol 500mg', 'Paracetamol', 'Effective pain relief and fever reduction. Trusted by doctors for over 50 years.', 1, 45.00, 40.00, 150, 'Cipla Ltd', 'Paracip', FALSE, 'PARA500', '8901234567890', 4.5, 128, TRUE, '["pain", "fever", "headache", "analgesic"]', 'active'),
('Ibuprofen 400mg', 'Ibuprofen', 'Anti-inflammatory pain medication for muscle pain, arthritis and menstrual cramps.', 1, 65.00, NULL, 100, 'Sun Pharma', 'Ibugesic', FALSE, 'IBU400', '8901234567891', 4.3, 89, TRUE, '["pain", "inflammation", "arthritis"]', 'active'),
('Amoxicillin 500mg', 'Amoxicillin', 'Broad-spectrum antibiotic for bacterial infections. Consult doctor before use.', 2, 120.00, 110.00, 75, 'Pfizer', 'Amoxil', TRUE, 'AMOX500', '8901234567892', 4.6, 67, FALSE, '["antibiotic", "infection", "bacterial"]', 'active'),
('Vitamin C 500mg', 'Ascorbic Acid', 'Immune system support supplement with antioxidant properties.', 3, 85.00, 75.00, 200, 'Dabur', 'C-Vit', FALSE, 'VITC500', '8901234567893', 4.4, 156, TRUE, '["vitamin", "immunity", "antioxidant"]', 'active'),
('Vitamin D3 1000 IU', 'Cholecalciferol', 'Essential for bone health and immune support. Especially important in winter months.', 3, 95.00, NULL, 180, 'Zandu', 'Vitamin D3', FALSE, 'VITD1000', '8901234567894', 4.7, 203, TRUE, '["vitamin", "bone", "immunity"]', 'active'),
('Cold & Flu Tablets', 'Paracetamol+Phenylephrine', 'Comprehensive relief from cold and flu symptoms including congestion and fever.', 4, 55.00, 50.00, 120, 'Himalaya', 'Koflet', FALSE, 'COLDTAB', '8901234567895', 4.2, 94, FALSE, '["cold", "flu", "congestion"]', 'active'),
('Antacid Liquid', 'Aluminium Hydroxide', 'Fast relief from acidity, heartburn and indigestion. Mint flavored.', 5, 75.00, NULL, 90, 'GSK', 'Digene', FALSE, 'ANTACID', '8901234567896', 4.1, 78, FALSE, '["antacid", "acidity", "heartburn"]', 'active'),
('Aspirin 75mg', 'Aspirin', 'Low dose aspirin for heart health and blood thinning. Doctor consultation recommended.', 6, 40.00, 35.00, 250, 'Bayer', 'Aspirin', FALSE, 'ASP75', '8901234567897', 4.5, 145, TRUE, '["heart", "blood thinner", "prevention"]', 'active'),
('Metformin 500mg', 'Metformin', 'Type 2 diabetes management medication. Controls blood sugar levels effectively.', 7, 35.00, NULL, 80, 'Lupin', 'Glycomet', TRUE, 'MET500', '8901234567898', 4.6, 189, FALSE, '["diabetes", "blood sugar", "metformin"]', 'active'),
('Aloe Vera Gel', 'Aloe Barbadensis', 'Soothing skin treatment for burns, cuts and dry skin. 100% natural.', 8, 125.00, 110.00, 60, 'Patanjali', 'Aloe Vera', FALSE, 'ALOE100', '8901234567899', 4.3, 67, FALSE, '["skin", "aloe", "natural"]', 'active'),
('Folic Acid 5mg', 'Folic Acid', 'Essential for pregnancy and women\'s health. Prevents birth defects.', 9, 45.00, NULL, 140, 'Abbott', 'Folvite', FALSE, 'FOL5', '8901234567900', 4.8, 112, TRUE, '["pregnancy", "women", "folic acid"]', 'active'),
('Biotin 10mg', 'Biotin', 'Promotes healthy hair, skin and nails. Essential for metabolism.', 10, 110.00, 100.00, 85, 'HealthKart', 'Biotin', FALSE, 'BIOT10', '8901234567901', 4.4, 56, FALSE, '["hair", "skin", "nails", "biotin"]', 'active'),
('Loratadine 10mg', 'Loratadine', 'Non-drowsy allergy relief. 24 hour protection from allergies.', 13, 95.00, NULL, 70, 'Cipla', 'Lorfast', FALSE, 'LOR10', '8901234567902', 4.5, 88, TRUE, '["allergy", "antihistamine", "non-drowsy"]', 'active'),
('Omeprazole 20mg', 'Omeprazole', 'Treats stomach ulcers and acid reflux. Long lasting relief.', 5, 150.00, 135.00, 55, 'Sun Pharma', 'Omez', FALSE, 'OME20', '8901234567903', 4.6, 134, FALSE, '["ulcer", "acid reflux", "stomach"]', 'active');

-- ========================================
-- Create Views for Common Queries
-- ========================================

-- View for User Dashboard Summary
CREATE VIEW user_dashboard_summary AS
SELECT 
    u.id as user_id,
    u.name as user_name,
    u.email,
    COUNT(DISTINCT c.id) as cart_items,
    COUNT(DISTINCT s.id) as active_subscriptions,
    COUNT(DISTINCT w.id) as wishlist_items,
    COUNT(DISTINCT o.id) as total_orders,
    COALESCE(SUM(CASE WHEN o.status = 'delivered' THEN o.total_amount ELSE 0 END), 0) as total_spent,
    COUNT(DISTINCT CASE WHEN o.status = 'pending' THEN o.id END) as pending_orders
FROM register u
LEFT JOIN cart c ON u.id = c.user_id
LEFT JOIN subscriptions s ON u.id = s.user_id AND s.status = 'active'
LEFT JOIN wishlist w ON u.id = w.user_id
LEFT JOIN orders o ON u.id = o.user_id
GROUP BY u.id, u.name, u.email;

-- View for Popular Medicines
CREATE VIEW popular_medicines AS
SELECT 
    m.*,
    c.name as category_name,
    COUNT(oi.id) as order_count,
    AVG(mr.rating) as avg_rating,
    COUNT(mr.id) as review_count
FROM medicines m
LEFT JOIN categories c ON m.category_id = c.id
LEFT JOIN order_items oi ON m.id = oi.medicine_id
LEFT JOIN medicine_reviews mr ON m.id = mr.medicine_id AND mr.status = 'approved'
WHERE m.status = 'active'
GROUP BY m.id
ORDER BY order_count DESC, avg_rating DESC;

-- View for Subscription Analytics
CREATE VIEW subscription_analytics AS
SELECT 
    s.*,
    m.name as medicine_name,
    u.name as user_name,
    DATEDIFF(s.next_delivery_date, CURDATE()) as days_until_delivery,
    CASE 
        WHEN s.next_delivery_date < CURDATE() THEN 'overdue'
        WHEN DATEDIFF(s.next_delivery_date, CURDATE()) <= 3 THEN 'due_soon'
        WHEN DATEDIFF(s.next_delivery_date, CURDATE()) <= 7 THEN 'upcoming'
        ELSE 'scheduled'
    END as delivery_status
FROM subscriptions s
JOIN medicines m ON s.medicine_id = m.id
JOIN register u ON s.user_id = u.id
WHERE s.status = 'active';

-- ========================================
-- Create Triggers for Data Integrity
-- ========================================

-- Update medicine rating when review is added/updated
DELIMITER //
CREATE TRIGGER update_medicine_rating_after_review
    AFTER INSERT ON medicine_reviews
    FOR EACH ROW
BEGIN
    UPDATE medicines 
    SET 
        rating = (
            SELECT COALESCE(AVG(rating), 0) 
            FROM medicine_reviews 
            WHERE medicine_id = NEW.medicine_id AND status = 'approved'
        ),
        review_count = (
            SELECT COUNT(*) 
            FROM medicine_reviews 
            WHERE medicine_id = NEW.medicine_id AND status = 'approved'
        )
    WHERE id = NEW.medicine_id;
END//
DELIMITER ;

-- Log user activity
DELIMITER //
CREATE TRIGGER log_user_activity_after_order
    AFTER INSERT ON orders
    FOR EACH ROW
BEGIN
    INSERT INTO user_activity_log (user_id, activity_type, description, related_id, related_type)
    VALUES (NEW.user_id, 'order_created', CONCAT('Order ', NEW.order_number, ' placed'), NEW.id, 'order');
END//
DELIMITER ;

-- ========================================
-- Create Stored Procedures
-- ========================================

DELIMITER //
-- Procedure to get user dashboard data
CREATE PROCEDURE GetUserDashboard(IN user_id_param INT)
BEGIN
    SELECT 
        u.name,
        u.email,
        COUNT(DISTINCT c.id) as cart_items,
        COALESCE(SUM(c.quantity * c.price), 0) as cart_total,
        COUNT(DISTINCT s.id) as active_subscriptions,
        COUNT(DISTINCT w.id) as wishlist_items,
        COUNT(DISTINCT o.id) as total_orders,
        COALESCE(SUM(CASE WHEN o.status = 'delivered' THEN o.total_amount ELSE 0 END), 0) as total_spent
    FROM register u
    LEFT JOIN cart c ON u.id = c.user_id
    LEFT JOIN subscriptions s ON u.id = s.user_id AND s.status = 'active'
    LEFT JOIN wishlist w ON u.id = w.user_id
    LEFT JOIN orders o ON u.id = o.user_id
    WHERE u.id = user_id_param
    GROUP BY u.id, u.name, u.email;
END//

-- Procedure to process subscription delivery
CREATE PROCEDURE ProcessSubscriptionDelivery(IN subscription_id_param INT)
BEGIN
    DECLARE v_subscription_type VARCHAR(20);
    DECLARE v_next_delivery DATE;
    DECLARE v_quantity INT;
    DECLARE v_medicine_id INT;
    DECLARE v_user_id INT;
    
    -- Get subscription details
    SELECT subscription_type, next_delivery_date, quantity, medicine_id, user_id
    INTO v_subscription_type, v_next_delivery, v_quantity, v_medicine_id, v_user_id
    FROM subscriptions 
    WHERE id = subscription_id_param AND status = 'active';
    
    -- Calculate next delivery date
    CASE v_subscription_type
        WHEN 'weekly' THEN SET v_next_delivery = DATE_ADD(v_next_delivery, INTERVAL 1 WEEK);
        WHEN 'monthly' THEN SET v_next_delivery = DATE_ADD(v_next_delivery, INTERVAL 1 MONTH);
        WHEN 'quarterly' THEN SET v_next_delivery = DATE_ADD(v_next_delivery, INTERVAL 3 MONTH);
        WHEN 'yearly' THEN SET v_next_delivery = DATE_ADD(v_next_delivery, INTERVAL 1 YEAR);
    END CASE;
    
    -- Update subscription
    UPDATE subscriptions 
    SET next_delivery_date = v_next_delivery,
        total_deliveries = total_deliveries + 1,
        updated_at = NOW()
    WHERE id = subscription_id_param;
    
    -- Create delivery record
    INSERT INTO subscription_deliveries (subscription_id, delivery_number, delivery_date, status)
    VALUES (subscription_id_param, 
            (SELECT COALESCE(MAX(delivery_number), 0) + 1 FROM subscription_deliveries WHERE subscription_id = subscription_id_param),
            CURDATE(), 
            'pending');
END//

DELIMITER ;

-- ========================================
-- Setup Complete
-- ========================================
SELECT 'KK Pharma User Dashboard Database Setup Complete!' as message;
