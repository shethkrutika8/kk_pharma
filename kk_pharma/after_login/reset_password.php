<?php
require_once 'config.php';

// Reset admin password
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$sql = "UPDATE admin SET password = ? WHERE username = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hashed_password);

if ($stmt->execute()) {
    echo "Password reset successfully!<br>";
    echo "New login credentials:<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br><br>";
    echo '<a href="login.php">Go to Login</a>';
} else {
    echo "Error resetting password: " . $conn->error;
}
?>
