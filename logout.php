<?php
<<<<<<< HEAD
require_once 'kk_pharma/after_login/config.php';

// Destroy all session variables
session_unset();
session_destroy();

// Redirect to login page
=======
session_start();
session_unset();
session_destroy();
>>>>>>> c6775067838e23982daa6c2b4f2a0e39c1aeb335
header("Location: login.php");
exit();
?>
