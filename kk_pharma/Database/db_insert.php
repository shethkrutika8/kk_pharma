<?php

$con = mysqli_connect("localhost", "root", "");

try {
    mysqli_select_db($con, "25_project");
} catch (Exception $e) {
    echo "Error in connecting with DB: " . $e->getMessage();
}

//$create_table = "CREATE TABLE register_1 (
//    id INT AUTO_INCREMENT PRIMARY KEY,
//    fname VARCHAR(50),
//    lname VARCHAR(50),
//    email VARCHAR(100) UNIQUE,
//    mobile VARCHAR(15) UNIQUE,
//    dept VARCHAR(50),
//    exp INT,
//    address TEXT,
//    password VARCHAR(255)
//)";

//if (mysqli_query($con, $create_table)) {
//    echo "Table created successfully";
//} else {
//    echo "Error creating table: " . mysqli_error($con);
//}

//$create_table = "CREATE TABLE login (
//    email VARCHAR(100) UNIQUE,
//    password VARCHAR(255)
//)";
//
//if (mysqli_query($con, $create_table)) {
//    echo "Table created successfully";
//} else {
//    echo "Error creating table: " . mysqli_error($con);
//}

//$create_table = "CREATE TABLE contact  (
//    name varchar(50),
//    email varchar(50),
//    subject varchar(100),
//    message text
//)";
//if (mysqli_query($con, $create_table)) {
//    echo "Table created successfully";
//} else {
//    echo "Error creating table:" . mysqli_error($con);
//}

?>