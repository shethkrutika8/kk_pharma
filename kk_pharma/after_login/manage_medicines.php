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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $name = sanitizeInput($_POST['name'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $category = sanitizeInput($_POST['category'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $old_price = !empty($_POST['old_price']) ? (float)$_POST['old_price'] : null;
            $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
            $manufacturer = sanitizeInput($_POST['manufacturer'] ?? '');
            $expiry_date = $_POST['expiry_date'] ?? '';
            $prescription_required = isset($_POST['prescription_required']);
            $status = sanitizeInput($_POST['status'] ?? 'active');
            
            // Handle image upload
            $image_url = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../../images/medicines/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;
                
                // Check if image file is actual image
                $check = getimagesize($_FILES['image']['tmp_name']);
                if ($check !== false) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_url = '../images/medicines/' . $file_name;
                    }
                }
            }
            
            // Insert medicine
            $stmt = $conn->prepare("INSERT INTO medicines (name, description, category, price, old_price, stock_quantity, manufacturer, expiry_date, prescription_required, status, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdiiissss", $name, $description, $category, $price, $old_price, $stock_quantity, $manufacturer, $expiry_date, $prescription_required, $status, $image_url);
            
            if ($stmt->execute()) {
                $_SESSION['success_msg'] = "Medicine added successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to add medicine.";
            }
            header("Location: manage_medicines.php");
            exit();
            break;
            
        case 'edit':
            $medicine_id = (int)$_POST['medicine_id'];
            $name = sanitizeInput($_POST['name'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $category = sanitizeInput($_POST['category'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $old_price = !empty($_POST['old_price']) ? (float)$_POST['old_price'] : null;
            $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
            $manufacturer = sanitizeInput($_POST['manufacturer'] ?? '');
            $expiry_date = $_POST['expiry_date'] ?? '';
            $prescription_required = isset($_POST['prescription_required']);
            $status = sanitizeInput($_POST['status'] ?? 'active');
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../../images/medicines/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;
                
                $check = getimagesize($_FILES['image']['tmp_name']);
                if ($check !== false) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_url = '../images/medicines/' . $file_name;
                        
                        // Update with new image
                        $stmt = $conn->prepare("UPDATE medicines SET name=?, description=?, category=?, price=?, old_price=?, stock_quantity=?, manufacturer=?, expiry_date=?, prescription_required=?, status=?, image_url=? WHERE id=?");
                        $stmt->bind_param("sssdiiissssi", $name, $description, $category, $price, $old_price, $stock_quantity, $manufacturer, $expiry_date, $prescription_required, $status, $image_url, $medicine_id);
                        $stmt->execute();
                        $_SESSION['success_msg'] = "Medicine updated successfully!";
                        header("Location: manage_medicines.php");
                        exit();
                    }
                }
            }
            
            // Update without image change
            $stmt = $conn->prepare("UPDATE medicines SET name=?, description=?, category=?, price=?, old_price=?, stock_quantity=?, manufacturer=?, expiry_date=?, prescription_required=?, status=? WHERE id=?");
            $stmt->bind_param("sssdiiisssi", $name, $description, $category, $price, $old_price, $stock_quantity, $manufacturer, $expiry_date, $prescription_required, $status, $medicine_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_msg'] = "Medicine updated successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to update medicine.";
            }
            header("Location: manage_medicines.php");
            exit();
            break;
            
        case 'delete':
            $medicine_id = (int)$_POST['medicine_id'];
            $stmt = $conn->prepare("DELETE FROM medicines WHERE id = ?");
            $stmt->bind_param("i", $medicine_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_msg'] = "Medicine deleted successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to delete medicine.";
            }
            header("Location: manage_medicines.php");
            exit();
            break;
    }
}

// Handle search and filter
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Build query
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR description LIKE ? OR manufacturer LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if (!empty($category_filter)) {
    $where_conditions[] = "category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get medicines
$sql = "SELECT * FROM medicines $where_clause ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$medicines = $stmt->get_result();

// Get categories for filter
$categories = $conn->query("SELECT DISTINCT category FROM medicines ORDER BY category");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Medicines - PharmaCare Admin</title>
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
        .table-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .table-actions {
            white-space: nowrap;
        }
        .badge-stock {
            font-size: 0.75rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        }
        .medicine-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
                            <a class="nav-link active" href="manage_medicines.php">
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
                    <h1 class="h2">Manage Medicines</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="add_medicine.php" class="btn btn-primary btn-sm me-2">
                            <i class="bi bi-plus-circle"></i> Add Medicine
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportMedicines()">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">
                                <i class="bi bi-search"></i> Search
                            </label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" placeholder="Search medicines...">
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">
                                <i class="bi bi-tags"></i> Category
                            </label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($cat['category']) ?>" 
                                            <?= $category_filter == $cat['category'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['category']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">
                                <i class="bi bi-toggle-on"></i> Status
                            </label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $status_filter == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-funnel"></i> Filter
                                </button>
                                <a href="manage_medicines.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Results count -->
                <div class="mb-3">
                    <small class="text-muted">
                        Showing <?= $medicines->num_rows ?> medicines
                    </small>
                </div>

                <!-- Medicines Table -->
                <div class="card table-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Manufacturer</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($medicines->num_rows > 0): ?>
                                        <?php while ($medicine = $medicines->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($medicine['image_url'])): ?>
                                                    <img src="<?= htmlspecialchars($medicine['image_url']) ?>" 
                                                         alt="<?= htmlspecialchars($medicine['name']) ?>" 
                                                         class="medicine-image">
                                                <?php else: ?>
                                                    <div class="medicine-image bg-light d-flex align-items-center justify-content-center">
                                                        <i class="bi bi-capsules text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($medicine['name']) ?></strong>
                                                    <?php if ($medicine['prescription_required']): ?>
                                                        <span class="badge bg-warning ms-1">Rx</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($medicine['description'])): ?>
                                                        <br><small class="text-muted"><?= substr(htmlspecialchars($medicine['description']), 0, 50) ?>...</small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($medicine['category']) ?></td>
                                            <td>
                                                <div>
                                                    <strong>₹<?= number_format($medicine['price'], 2) ?></strong>
                                                    <?php if ($medicine['old_price'] > 0): ?>
                                                        <br><small class="text-muted text-decoration-line-through">₹<?= number_format($medicine['old_price'], 2) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-stock <?= $medicine['stock_quantity'] < 10 ? 'bg-warning' : 'bg-success' ?>">
                                                    <?= $medicine['stock_quantity'] ?>
                                                </span>
                                                <?php if ($medicine['stock_quantity'] == 0): ?>
                                                    <br><small class="text-danger">Out of stock</small>
                                                <?php elseif ($medicine['stock_quantity'] < 10): ?>
                                                    <br><small class="text-warning">Low stock</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($medicine['manufacturer']) ?></td>
                                            <td>
                                                <span class="badge <?= $medicine['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= ucfirst($medicine['status']) ?>
                                                </span>
                                            </td>
                                            <td class="table-actions">
                                                <a href="edit_medicine.php?id=<?= $medicine['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="view_medicine.php?id=<?= $medicine['id'] ?>" 
                                                   class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="toggleStatus(<?= $medicine['id'] ?>, '<?= $medicine['status'] ?>')" 
                                                        title="Toggle Status">
                                                    <i class="bi bi-toggle-on"></i>
                                                </button>
                                                <a href="delete_medicine.php?id=<?= $medicine['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this medicine?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                                <p class="text-muted">No medicines found</p>
                                                <a href="add_medicine.php" class="btn btn-primary">Add First Medicine</a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleStatus(id, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            if (confirm(`Are you sure you want to change status to ${newStatus}?`)) {
                window.location.href = `toggle_medicine_status.php?id=${id}&status=${newStatus}`;
            }
        }

        function exportMedicines() {
            // Simple CSV export
            let csv = 'ID,Name,Category,Price,Stock,Manufacturer,Status\n';
            
            <?php if ($medicines->num_rows > 0): ?>
                <?php 
                // Reset result pointer for export
                $medicines->data_seek(0);
                while ($medicine = $medicines->fetch_assoc()): 
                ?>
                    csv += '<?= $medicine['id'] ?>,"<?= addslashes($medicine['name']) ?>","<?= addslashes($medicine['category']) ?>",<?= $medicine['price'] ?>,<?= $medicine['stock_quantity'] ?>,"<?= addslashes($medicine['manufacturer']) ?>","<?= $medicine['status'] ?>"\n';
                <?php endwhile; ?>
            <?php endif; ?>
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'medicines_export.csv';
            a.click();
        }
    </script>
</body>
</html>
