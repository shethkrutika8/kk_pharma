<?php
require_once 'config.php';
requireAdminLogin();

$medicine_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$new_status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

if ($medicine_id <= 0 || !in_array($new_status, ['active', 'inactive'])) {
    header("Location: manage_medicines.php");
    exit();
}

// Update medicine status
$sql = "UPDATE medicines SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $new_status, $medicine_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Medicine status updated to " . ucfirst($new_status) . " successfully.";
} else {
    $_SESSION['error'] = "Error updating medicine status: " . $conn->error;
}

header("Location: manage_medicines.php");
exit();
?>
