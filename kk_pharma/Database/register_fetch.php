<?php
include_once 'db_config.php'; // Ensure this file has your $con connection

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
    echo "Table 'register' created successfully";
} else {
    echo "Error creating table: " . mysqli_error($con);
}
?>
