<?php
require_once 'config.php';
requireAdminLogin();

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
        $status = 'active'; // Default to active if invalid
    }
    
    // Validation
    if (empty($name)) $errors[] = "Medicine name is required";
    if (empty($category)) $errors[] = "Category is required";
    if ($price <= 0) $errors[] = "Price must be greater than 0";
    if ($stock_quantity < 0) $errors[] = "Stock quantity cannot be negative";
    if (empty($manufacturer)) $errors[] = "Manufacturer is required";
    
    if (empty($errors)) {
        $sql = "UPDATE medicines SET name = ?, description = ?, category = ?, price = ?, old_price = ?, 
                stock_quantity = ?, manufacturer = ?, expiry_date = ?, image_url = ?, 
                prescription_required = ?, status = ? WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssddisssisi", $name, $description, $category, $price, $old_price, 
                          $stock_quantity, $manufacturer, $expiry_date, $image_url, 
                          $prescription_required, $status, $medicine_id);
        
        if ($stmt->execute()) {
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
        .medicine-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
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
                            <a class="nav-link" href="add_medicine.php">
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
                    <h1 class="h2">Edit Medicine</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="manage_medicines.php" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Back to Medicines
                        </a>
                        <a href="view_medicine.php?id=<?= $medicine_id ?>" class="btn btn-sm btn-outline-info">
                            <i class="bi bi-eye"></i> View
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

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card form-card">
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">
                                                <i class="bi bi-capsules"></i> Medicine Name *
                                            </label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?= htmlspecialchars($medicine['name']) ?>" 
                                                   required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="category" class="form-label">
                                                <i class="bi bi-tags"></i> Category *
                                            </label>
                                            <select class="form-select" id="category" name="category" required>
                                                <option value="">Select Category</option>
                                                <option value="Pain Relief" <?= $medicine['category'] == 'Pain Relief' ? 'selected' : '' ?>>Pain Relief</option>
                                                <option value="Antibiotics" <?= $medicine['category'] == 'Antibiotics' ? 'selected' : '' ?>>Antibiotics</option>
                                                <option value="Vitamins" <?= $medicine['category'] == 'Vitamins' ? 'selected' : '' ?>>Vitamins</option>
                                                <option value="Cough & Cold" <?= $medicine['category'] == 'Cough & Cold' ? 'selected' : '' ?>>Cough & Cold</option>
                                                <option value="Diabetes" <?= $medicine['category'] == 'Diabetes' ? 'selected' : '' ?>>Diabetes</option>
                                                <option value="Heart" <?= $medicine['category'] == 'Heart' ? 'selected' : '' ?>>Heart</option>
                                                <option value="Skin" <?= $medicine['category'] == 'Skin' ? 'selected' : '' ?>>Skin</option>
                                                <option value="Other" <?= $medicine['category'] == 'Other' ? 'selected' : '' ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">
                                            <i class="bi bi-text-paragraph"></i> Description
                                        </label>
                                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($medicine['description']) ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="price" class="form-label">
                                                <i class="bi bi-currency-rupee"></i> Price *
                                            </label>
                                            <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                                   value="<?= htmlspecialchars($medicine['price']) ?>" 
                                                   required>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="old_price" class="form-label">
                                                <i class="bi bi-currency-rupee"></i> Old Price
                                            </label>
                                            <input type="number" step="0.01" class="form-control" id="old_price" name="old_price" 
                                                   value="<?= htmlspecialchars($medicine['old_price']) ?>">
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="stock_quantity" class="form-label">
                                                <i class="bi bi-box"></i> Stock Quantity *
                                            </label>
                                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                                   value="<?= htmlspecialchars($medicine['stock_quantity']) ?>" 
                                                   required>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="expiry_date" class="form-label">
                                                <i class="bi bi-calendar"></i> Expiry Date
                                            </label>
                                            <input type="date" class="form-control" id="expiry_date" name="expiry_date" 
                                                   value="<?= htmlspecialchars($medicine['expiry_date']) ?>">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="manufacturer" class="form-label">
                                                <i class="bi bi-building"></i> Manufacturer *
                                            </label>
                                            <input type="text" class="form-control" id="manufacturer" name="manufacturer" 
                                                   value="<?= htmlspecialchars($medicine['manufacturer']) ?>" 
                                                   required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="image_url" class="form-label">
                                                <i class="bi bi-image"></i> Image URL
                                            </label>
                                            <input type="url" class="form-control" id="image_url" name="image_url" 
                                                   value="<?= htmlspecialchars($medicine['image_url']) ?>"
                                                   placeholder="https://example.com/image.jpg">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="status" class="form-label">
                                                <i class="bi bi-toggle-on"></i> Status
                                            </label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active" <?= $medicine['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                                <option value="inactive" <?= $medicine['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="prescription_required" 
                                                       name="prescription_required" <?= $medicine['prescription_required'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="prescription_required">
                                                    <i class="bi bi-file-medical"></i> Prescription Required
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <button type="button" class="btn btn-danger" 
                                                    onclick="deleteMedicine()">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </div>
                                        <div>
                                            <a href="manage_medicines.php" class="btn btn-secondary me-2">Cancel</a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-save"></i> Update Medicine
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Medicine Preview</h5>
                            </div>
                            <div class="card-body text-center">
                                <?php if (!empty($medicine['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($medicine['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($medicine['name']) ?>" 
                                         class="medicine-preview mb-3">
                                <?php else: ?>
                                    <div class="medicine-preview bg-light d-flex align-items-center justify-content-center mx-auto mb-3">
                                        <i class="bi bi-capsules fs-1 text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <h6><?= htmlspecialchars($medicine['name']) ?></h6>
                                <p class="text-muted"><?= htmlspecialchars($medicine['category']) ?></p>
                                <h5>₹<?= number_format($medicine['price'], 2) ?></h5>
                                <?php if ($medicine['old_price'] > 0): ?>
                                    <small class="text-muted text-decoration-line-through">₹<?= number_format($medicine['old_price'], 2) ?></small>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <span class="badge <?= $medicine['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= ucfirst($medicine['status']) ?>
                                    </span>
                                    <?php if ($medicine['prescription_required']): ?>
                                        <span class="badge bg-warning">Rx Required</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <strong>Stock:</strong> <?= $medicine['stock_quantity'] ?> units<br>
                                        <strong>Manufacturer:</strong> <?= htmlspecialchars($medicine['manufacturer']) ?><br>
                                        <?php if ($medicine['expiry_date']): ?>
                                            <strong>Expiry:</strong> <?= date('M d, Y', strtotime($medicine['expiry_date'])) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteMedicine() {
            if (confirm('Are you sure you want to delete this medicine? This action cannot be undone.')) {
                window.location.href = 'delete_medicine.php?id=<?= $medicine_id ?>';
            }
        }

        // Preview image when URL changes
        document.getElementById('image_url').addEventListener('change', function() {
            const url = this.value;
            const preview = document.querySelector('.medicine-preview');
            
            if (url) {
                preview.src = url;
                preview.onerror = function() {
                    this.src = '';
                    this.style.display = 'none';
                };
            }
        });
    </script>
</body>
</html>
