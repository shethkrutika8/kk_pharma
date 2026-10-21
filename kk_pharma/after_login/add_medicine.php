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
    
    // Validation
    if (empty($name)) $errors[] = "Medicine name is required";
    if (empty($category)) $errors[] = "Category is required";
    if ($price <= 0) $errors[] = "Price must be greater than 0";
    if ($stock_quantity < 0) $errors[] = "Stock quantity cannot be negative";
    if (empty($manufacturer)) $errors[] = "Manufacturer is required";
    
    if (empty($errors)) {
        $sql = "INSERT INTO medicines (name, description, category, price, old_price, stock_quantity, 
                manufacturer, expiry_date, image_url, prescription_required, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdidisssbi", $name, $description, $category, $price, $old_price, 
                          $stock_quantity, $manufacturer, $expiry_date, $image_url, 
                          $prescription_required, $status);
        
        if ($stmt->execute()) {
            $success = "Medicine added successfully!";
            // Clear form
            $_POST = [];
        } else {
            $errors[] = "Error adding medicine: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medicine - PharmaCare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: white;
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .main-content {
            padding: 20px;
        }
        .form-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        }
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
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_medicines.php">
                                <i class="bi bi-capsules"></i> Medicines
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="add_medicine.php">
                                <i class="bi bi-plus-circle"></i> Add Medicine
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="bi bi-tags"></i> Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="bi bi-graph-up"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Add New Medicine</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="manage_medicines.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Medicines
                        </a>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <?= displayAlert($error, 'danger') ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <?= displayAlert($success, 'success') ?>
                <?php endif; ?>

                <div class="card form-card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">
                                        <i class="bi bi-capsules"></i> Medicine Name *
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" 
                                           required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">
                                        <i class="bi bi-tags"></i> Category *
                                    </label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="Pain Relief" <?= isset($_POST['category']) && $_POST['category'] == 'Pain Relief' ? 'selected' : '' ?>>Pain Relief</option>
                                        <option value="Antibiotics" <?= isset($_POST['category']) && $_POST['category'] == 'Antibiotics' ? 'selected' : '' ?>>Antibiotics</option>
                                        <option value="Vitamins" <?= isset($_POST['category']) && $_POST['category'] == 'Vitamins' ? 'selected' : '' ?>>Vitamins</option>
                                        <option value="Cough & Cold" <?= isset($_POST['category']) && $_POST['category'] == 'Cough & Cold' ? 'selected' : '' ?>>Cough & Cold</option>
                                        <option value="Diabetes" <?= isset($_POST['category']) && $_POST['category'] == 'Diabetes' ? 'selected' : '' ?>>Diabetes</option>
                                        <option value="Heart" <?= isset($_POST['category']) && $_POST['category'] == 'Heart' ? 'selected' : '' ?>>Heart</option>
                                        <option value="Skin" <?= isset($_POST['category']) && $_POST['category'] == 'Skin' ? 'selected' : '' ?>>Skin</option>
                                        <option value="Other" <?= isset($_POST['category']) && $_POST['category'] == 'Other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    <i class="bi bi-text-paragraph"></i> Description
                                </label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="price" class="form-label">
                                        <i class="bi bi-currency-rupee"></i> Price *
                                    </label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                           value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '' ?>" 
                                           required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="old_price" class="form-label">
                                        <i class="bi bi-currency-rupee"></i> Old Price
                                    </label>
                                    <input type="number" step="0.01" class="form-control" id="old_price" name="old_price" 
                                           value="<?= isset($_POST['old_price']) ? htmlspecialchars($_POST['old_price']) : '' ?>">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="stock_quantity" class="form-label">
                                        <i class="bi bi-box"></i> Stock Quantity *
                                    </label>
                                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                           value="<?= isset($_POST['stock_quantity']) ? htmlspecialchars($_POST['stock_quantity']) : '0' ?>" 
                                           required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="expiry_date" class="form-label">
                                        <i class="bi bi-calendar"></i> Expiry Date
                                    </label>
                                    <input type="date" class="form-control" id="expiry_date" name="expiry_date" 
                                           value="<?= isset($_POST['expiry_date']) ? htmlspecialchars($_POST['expiry_date']) : '' ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="manufacturer" class="form-label">
                                        <i class="bi bi-building"></i> Manufacturer *
                                    </label>
                                    <input type="text" class="form-control" id="manufacturer" name="manufacturer" 
                                           value="<?= isset($_POST['manufacturer']) ? htmlspecialchars($_POST['manufacturer']) : '' ?>" 
                                           required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="image_url" class="form-label">
                                        <i class="bi bi-image"></i> Image URL
                                    </label>
                                    <input type="url" class="form-control" id="image_url" name="image_url" 
                                           value="<?= isset($_POST['image_url']) ? htmlspecialchars($_POST['image_url']) : '' ?>"
                                           placeholder="https://example.com/image.jpg">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">
                                        <i class="bi bi-toggle-on"></i> Status
                                    </label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" <?= !isset($_POST['status']) || $_POST['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= isset($_POST['status']) && $_POST['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="prescription_required" 
                                               name="prescription_required" <?= isset($_POST['prescription_required']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="prescription_required">
                                            <i class="bi bi-file-medical"></i> Prescription Required
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="manage_medicines.php" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Add Medicine
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
