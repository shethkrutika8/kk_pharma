<?php
//step-1 connect with database
$con = mysqli_connect("localhost", "root", "");

//step-2 create database
$create_db = "CREATE DATABASE IF NOT EXISTS kk_pharma";
mysqli_query($con, $create_db);

//step-3 select database
try {
    mysqli_select_db($con, "kk_pharma");
} catch (Exception) {
    echo "Error in connecting with DB";
}

//step-4 create table is a one time process so we can comment this code after creating table


//step-4 crate tabele  is a onr time process so we can comment this code after creating table


// include_once 'db_config.php'; // Removed recursive inclusion

$create_table = "CREATE TABLE IF NOT EXISTS register (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mobile BIGINT(10) NOT NULL,
    department VARCHAR(50) NOT NULL,
    experience VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_picture TEXT,
    vkey VARCHAR(255) NULL,
    verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($con, $create_table)) {
    // Add columns if table already existed without them
    $check_vkey = mysqli_query($con, "SHOW COLUMNS FROM register LIKE 'vkey'");
    if (mysqli_num_rows($check_vkey) == 0) {
        mysqli_query($con, "ALTER TABLE register ADD COLUMN vkey VARCHAR(255) NULL, ADD COLUMN verified TINYINT(1) DEFAULT 0");
    }

    $check_otp = mysqli_query($con, "SHOW COLUMNS FROM register LIKE 'reset_otp'");
    if (mysqli_num_rows($check_otp) == 0) {
        mysqli_query($con, "ALTER TABLE register ADD COLUMN reset_otp VARCHAR(10) NULL, ADD COLUMN reset_otp_expiry DATETIME NULL");
    }
} else {
    // echo "Error creating table: " . mysqli_error($con);
}

$create_contact_table = "CREATE TABLE IF NOT EXISTS contact_inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(150),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($con, $create_contact_table)) {
    // echo "Table 'contact_inquiries' created successfully";
} else {
    // echo "Error creating table: " . mysqli_error($con);
}

$create_admins_table = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($con, $create_admins_table)) {
    // Check if the default admin is already present
    $check_admin = mysqli_query($con, "SELECT id FROM admins WHERE user_id = 'admin'");
    if (mysqli_num_rows($check_admin) == 0) {
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = "INSERT INTO admins (user_id, password) VALUES ('admin', '$hashed_password')";
        mysqli_query($con, $insert_admin);
    }
} else {
    // echo "Error creating table: " . mysqli_error($con);
}

// ---------------------------------------------------------
// DYNAMIC CONTENT SETUP - Separate Tables per Page
// ---------------------------------------------------------

$page_tables = [
    'home_content',
    'about_content',
    'contact_content',
    'auth_content'
];

foreach ($page_tables as $table) {
    $create_content_table = "CREATE TABLE IF NOT EXISTS $table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        content_key VARCHAR(100) NOT NULL UNIQUE,
        content_value TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    mysqli_query($con, $create_content_table);
}

// Insert default content if empty
$defaults = [
    // Home Page
    'home_content' => [
        ['home_carousel1_title', 'WHO-GMP Certified Manufacturing'],
        ['home_carousel1_subtitle', 'International standards you can rely on'],
        ['home_carousel2_title', 'Innovation Driven Healthcare'],
        ['home_carousel2_subtitle', 'Research, technology and patient care combined'],
        ['home_about_title', 'About KK Pharma'],
        ['home_about_desc', 'KK Pharma is a fast-growing pharmaceutical company committed to manufacturing high-quality, affordable and reliable medicines that improve lives across the globe.'],
        ['home_mission_title', 'Our Mission'],
        ['home_mission_desc', 'To enhance patient wellbeing by providing safe, effective and affordable medicines.'],
        ['home_vision_title', 'Our Vision'],
        ['home_vision_desc', 'To become a globally trusted pharmaceutical brand delivering excellence.'],
        ['home_values_title', 'Our Values'],
        ['home_values_desc', 'Quality, Integrity, Innovation and Patient-Centric Care.'],
        ['home_stats_exp', '10+'],
        ['home_stats_exp_text', 'Years of Experience'],
        ['home_stats_products', '150+'],
        ['home_stats_products_text', 'Products Portfolio'],
        ['home_stats_countries', '20+'],
        ['home_stats_countries_text', 'Countries Served'],
        ['home_stats_quality', '100%'],
        ['home_stats_quality_text', 'Quality Compliance'],
        ['home_products_title', 'Our Product Range'],
        ['home_products_desc', 'We offer a comprehensive portfolio covering multiple therapeutic segments.'],
        ['home_product1_title', 'Tablets'],
        ['home_product1_desc', 'Antibiotics, Analgesics, Anti-inflammatory & Vitamins'],
        ['home_product2_title', 'Syrups'],
        ['home_product2_desc', 'Pediatric, Cough & Nutritional Formulations'],
        ['home_product3_title', 'Capsules'],
        ['home_product3_desc', 'Nutraceuticals and Specialty Supplements'],
        ['home_product4_title', 'Injectables'],
        ['home_product4_desc', 'Critical Care & Hospital Use Medicines'],
        ['contact_info_title', 'KK Pharma Pvt. Ltd.'],
        ['contact_email', 'info@kkpharma.com'],
        ['contact_phone', '+91 98765 43210'],
        ['contact_address', 'Ahmedabad, Gujarat, India'],
    ],
    // About Page
    'about_content' => [
        ['about_hero_title', 'Advancing Healthcare with Trust & Innovation'],
        ['about_hero_desc', 'KK Pharmaceutical is a trusted name in the pharmaceutical industry, dedicated to manufacturing high-quality, affordable medicines. With decades of experience, we combine innovation, ethics, and patient-centric care to improve lives worldwide.'],
        ['about_section2_title', 'Our Mission, Vision & Commitment'],
        ['about_mission_title', '🎯 Our Mission'],
        ['about_mission_desc', 'To develop, manufacture, and deliver high-quality pharmaceutical products that improve health outcomes and enhance the quality of life for patients across all regions.'],
        ['about_vision_title', '👁 Our Vision'],
        ['about_vision_desc', 'To be a globally trusted pharmaceutical company recognized for innovation, ethical practices, and affordable healthcare solutions.'],
        ['about_commitment_title', '🤝 Our Commitment'],
        ['about_commitment_desc', 'We are committed to patient safety, regulatory compliance, continuous improvement, and sustainable healthcare development.'],
        ['about_timeline1_title', '💊 Healthcare Leadership'],
        ['about_timeline1_desc', 'Our state-of-the-art manufacturing facilities and strict quality control systems ensure world-class pharmaceutical products that meet international standards.'],
        ['about_timeline2_title', '🌍 Global Presence'],
        ['about_timeline2_desc', 'KK Pharmaceutical continues to expand its global footprint, supplying essential medicines through strong international partnerships and distribution networks.'],
        ['about_values_title', 'Our Core Values'],
        ['about_value1_icon', '✔'],
        ['about_value1_title', 'Quality'],
        ['about_value1_desc', 'Uncompromised product excellence'],
        ['about_value2_icon', '❤️'],
        ['about_value2_title', 'Patient Focus'],
        ['about_value2_desc', 'Healthcare solutions that matter'],
        ['about_value3_icon', '⚙'],
        ['about_value3_title', 'Innovation'],
        ['about_value3_desc', 'Research-driven growth'],
        ['about_value4_icon', '🤝'],
        ['about_value4_title', 'Integrity'],
        ['about_value4_desc', 'Ethical & transparent practices'],
    ],
    // Contact Page
    'contact_content' => [
        ['contact_page_about_title', 'About KK Pharmaceutical'],
        ['contact_page_about_desc1', 'KK Pharmaceutical is a research-driven pharmaceutical company focused on developing, manufacturing, and distributing high-quality medicines across therapeutic segments.'],
        ['contact_page_about_desc2', 'Our operations comply with WHO-GMP standards, ensuring safety, efficacy, and consistency in every product we deliver.'],
        ['contact_page_corporate_title', '📍 Corporate Office'],
        ['contact_page_corporate_desc', 'Pharma Industrial Estate, India'],
        ['contact_page_hours_title', '🕒 Business Hours'],
        ['contact_hours1', 'Monday – Friday: 9:00 AM – 6:00 PM'],
        ['contact_hours2', 'Saturday: 9:00 AM – 2:00 PM'],
        ['contact_hours3', 'Sunday: Closed'],
        ['contact_dept1_title', '📞 Sales & Marketing'],
        ['contact_dept1_email', 'sales@kkpharma.com'],
        ['contact_dept1_phone', '+91 98765 11111'],
        ['contact_dept2_title', '🧪 Medical & QA'],
        ['contact_dept2_email', 'medical@kkpharma.com'],
        ['contact_dept2_phone', '+91 98765 22222'],
        ['contact_dept3_title', '📦 Distribution'],
        ['contact_dept3_email', 'distribution@kkpharma.com'],
        ['contact_dept3_phone', '+91 98765 33333'],
        ['contact_dept4_title', '⚠️ Pharmacovigilance'],
        ['contact_dept4_email', 'safety@kkpharma.com'],
        ['contact_dept4_desc', 'Report adverse drug reactions'],
        ['contact_faq_title', 'Frequently Asked Questions'],
        ['contact_faq1_q', 'Do you offer third-party manufacturing?'],
        ['contact_faq1_a', 'Yes, we provide WHO-GMP certified manufacturing services.'],
        ['contact_faq2_q', 'How can I become a distributor?'],
        ['contact_faq2_a', 'Contact our sales department with your company profile.'],
        ['contact_faq3_q', 'Are your products regulatory approved?'],
        ['contact_faq3_a', 'Yes, all products comply with national regulatory standards.'],
    ],
    // Login / Register Pages
    'auth_content' => [
        ['auth_overlay_title', 'KK Pharma'],
        ['auth_overlay_desc', 'Trusted Pharmaceutical Solutions<br>Quality • Safety • Innovation'],
        ['login_box_title', 'Login'],
        ['login_box_desc', 'Access your KK Pharma account'],
        ['register_box_title', 'Create Account'],
        ['register_box_desc', 'Join KK Pharma']
    ]
];

foreach ($page_tables as $table) {
    $check_empty = mysqli_query($con, "SELECT COUNT(*) as count FROM $table");
    $row = mysqli_fetch_assoc($check_empty);
    
    if ($row['count'] == 0 && isset($defaults[$table])) {
        $stmt = $con->prepare("INSERT IGNORE INTO $table (content_key, content_value) VALUES (?, ?)");
        foreach ($defaults[$table] as $item) {
            $stmt->bind_param("ss", $item[0], $item[1]);
            $stmt->execute();
        }
        $stmt->close();
    }
}

// Global scope fetching of site contents so all pages can access it
global $site_content;
$site_content = [];

foreach ($page_tables as $table) {
    $content_query = mysqli_query($con, "SELECT content_key, content_value FROM $table");
    if ($content_query) {
        while($row = mysqli_fetch_assoc($content_query)) {
             $site_content[$row['content_key']] = $row['content_value'];
        }
    }
}

if (!function_exists('get_content')) {
    function get_content($key, $default_fallback = '') {
        global $site_content;
        return $site_content[$key] ?? $default_fallback;
    }
}
?>
