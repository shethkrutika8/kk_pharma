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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($con, $create_table)) {
    // echo "Table 'register' created successfully";
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
?>
