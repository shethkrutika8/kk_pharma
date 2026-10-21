<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Check if user is logged in
if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get medicine ID
$medicine_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($medicine_id <= 0) {
    header("Location: manage_medicines.php");
    exit();
}

// Get medicine details
$sql = "SELECT * FROM medicines WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $medicine_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: manage_medicines.php");
    exit();
}

$medicine = $result->fetch_assoc();

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $category = sanitizeInput($_POST['category']);
    $price = floatval($_POST['price']);
    $old_price = !empty($_POST['old_price']) ? floatval($_POST['old_price']) : null;
    $stock_quantity = intval($_POST['stock_quantity']);
    $manufacturer = sanitizeInput($_POST['manufacturer']);
    $expiry_date = sanitizeInput($_POST['expiry_date']);
    $image_url = sanitizeInput($_POST['image_url']);
    $prescription_required = isset($_POST['prescription_required']) ? 1 : 0;
    $status = sanitizeInput($_POST['status']);
    
    // Validate and fix status
    $status = trim($status);
    if (!in_array($status, ['active', 'inactive'])) {
        $status = 'active';
    }
    
    // Validation
    if (empty($name)) $errors[] = "Medicine name is required";
    if (empty($category)) $errors[] = "Category is required";
    if ($price <= 0) $errors[] = "Price must be greater than 0";
    if ($stock_quantity < 0) $errors[] = "Stock quantity cannot be negative";
    if (empty($manufacturer)) $errors[] = "Manufacturer is required";
    
    if (empty($errors)) {
        // Use simple SQL instead of prepared statement to avoid bind_param issues
        $old_price_sql = $old_price ? "'$old_price'" : "NULL";
        
        $sql = "UPDATE medicines SET 
                name = '" . $conn->real_escape_string($name) . "',
                description = '" . $conn->real_escape_string($description) . "',
                category = '" . $conn->real_escape_string($category) . "',
                price = $price,
                old_price = $old_price_sql,
                stock_quantity = $stock_quantity,
                manufacturer = '" . $conn->real_escape_string($manufacturer) . "',
                expiry_date = '" . $conn->real_escape_string($expiry_date) . "',
                image_url = '" . $conn->real_escape_string($image_url) . "',
                prescription_required = $prescription_required,
                status = '" . $conn->real_escape_string($status) . "'
                WHERE id = $medicine_id";
        
        if ($conn->query($sql)) {
            $success = "Medicine updated successfully!";
            // Refresh medicine data
            $result = $conn->query("SELECT * FROM medicines WHERE id = $medicine_id");
            $medicine = $result->fetch_assoc();
        } else {
            $errors[] = "Error updating medicine: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Medicine - PharmaCare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .sidebar { min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .sidebar .nav-link { color: white; padding: 12px 20px; margin: 5px 10px; border-radius: 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,0.2); }
        .main-content { padding: 20px; }
        .form-card { border-radius: 12px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-white text-center mb-4">
                        <h4><i class="bi bi-hospital"></i> PharmaCare</h4>
                        <small>Admin Panel</small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="admin_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="manage_medicines.php"><i class="bi bi-capsules"></i> Medicines</a></li>
                        <li class="nav-item"><a class="nav-link" href="add_medicine.php"><i class="bi bi-plus-circle"></i> Add Medicine</a></li>
                        <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit Medicine</h1>
                    <a href="manage_medicines.php" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Medicines
                    </a>
                </div>

                <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <div class="card form-card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Medicine Name *</label>
                                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($medicine['name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category *</label>
                                    <select class="form-select" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="Pain Relief" <?= $medicine['category'] == 'Pain Relief' ? 'selected' : '' ?>>Pain Relief</option>
                                        <option value="Antibiotics" <?= $medicine['category'] == 'Antibiotics' ? 'selected' : '' ?>>Antibiotics</option>
                                        <option value="Vitamins" <?= $medicine['category'] == 'Vitamins' ? 'selected' : '' ?>>Vitamins</option>
                                        <option value="Cough & Cold" <?= $medicine['category'] == 'Cough & Cold' ? 'selected' : '' ?>>Cough & Cold</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($medicine['description']) ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Price *</label>
                                    <input type="number" step="0.01" class="form-control" name="price" value="<?= htmlspecialchars($medicine['price']) ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Old Price</label>
                                    <input type="number" step="0.01" class="form-control" name="old_price" value="<?= htmlspecialchars($medicine['old_price']) ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Stock Quantity *</label>
                                    <input type="number" class="form-control" name="stock_quantity" value="<?= htmlspecialchars($medicine['stock_quantity']) ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="active" <?= $medicine['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= $medicine['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Manufacturer *</label>
                                    <input type="text" class="form-control" name="manufacturer" value="<?= htmlspecialchars($medicine['manufacturer']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Image URL</label>
                                    <input type="url" class="form-control" name="image_url" value="<?= htmlspecialchars($medicine['image_url']) ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="prescription_required" <?= $medicine['prescription_required'] ? 'checked' : '' ?>>
                                    <label class="form-check-label">Prescription Required</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="manage_medicines.php" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update Medicine
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
