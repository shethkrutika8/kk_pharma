<?php
require_once 'kk_pharma/after_login/config.php';

// Destroy all session variables
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>
