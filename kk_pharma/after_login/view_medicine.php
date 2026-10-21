<?php
require_once 'config.php';
requireAdminLogin();

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Medicine - PharmaCare Admin</title>
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
        .view-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .medicine-image {
            max-width: 300px;
            max-height: 300px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .detail-label {
            font-weight: 600;
            color: #6c757d;
            min-width: 120px;
        }
        .badge-lg {
            font-size: 0.9rem;
            padding: 8px 12px;
        }
        .action-buttons {
            position: sticky;
            bottom: 20px;
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 -4px 15px rgba(0,0,0,0.1);
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
                    <h1 class="h2">Medicine Details</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="manage_medicines.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Medicines
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card view-card">
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-4 text-center">
                                        <?php if (!empty($medicine['image_url'])): ?>
                                            <img src="<?= htmlspecialchars($medicine['image_url']) ?>" 
                                                 alt="<?= htmlspecialchars($medicine['name']) ?>" 
                                                 class="medicine-image">
                                        <?php else: ?>
                                            <div class="medicine-image bg-light d-flex align-items-center justify-content-center">
                                                <i class="bi bi-capsules fs-1 text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-8">
                                        <h3><?= htmlspecialchars($medicine['name']) ?></h3>
                                        <p class="text-muted mb-3"><?= htmlspecialchars($medicine['category']) ?></p>
                                        
                                        <div class="mb-3">
                                            <h4 class="text-primary">₹<?= number_format($medicine['price'], 2) ?></h4>
                                            <?php if ($medicine['old_price'] > 0): ?>
                                                <span class="text-muted text-decoration-line-through">
                                                    ₹<?= number_format($medicine['old_price'], 2) ?>
                                                </span>
                                                <span class="badge bg-success ms-2">
                                                    Save ₹<?= number_format($medicine['old_price'] - $medicine['price'], 2) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="d-flex gap-2 mb-3">
                                            <span class="badge badge-lg <?= $medicine['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                <i class="bi bi-toggle-on"></i> <?= ucfirst($medicine['status']) ?>
                                            </span>
                                            <?php if ($medicine['prescription_required']): ?>
                                                <span class="badge badge-lg bg-warning">
                                                    <i class="bi bi-file-medical"></i> Prescription Required
                                                </span>
                                            <?php endif; ?>
                                            <span class="badge badge-lg <?= $medicine['stock_quantity'] < 10 ? 'bg-warning' : 'bg-info' ?>">
                                                <i class="bi bi-box"></i> Stock: <?= $medicine['stock_quantity'] ?>
                                            </span>
                                        </div>
                                        
                                        <?php if (!empty($medicine['description'])): ?>
                                            <div class="mb-3">
                                                <h6>Description</h6>
                                                <p><?= nl2br(htmlspecialchars($medicine['description'])) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="mb-3">Basic Information</h5>
                                        <table class="table table-borderless">
                                            <tr>
                                                <td class="detail-label">Medicine ID:</td>
                                                <td>#<?= str_pad($medicine['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="detail-label">Category:</td>
                                                <td><?= htmlspecialchars($medicine['category']) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="detail-label">Manufacturer:</td>
                                                <td><?= htmlspecialchars($medicine['manufacturer']) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="detail-label">Stock Quantity:</td>
                                                <td>
                                                    <span class="badge <?= $medicine['stock_quantity'] < 10 ? 'bg-warning' : 'bg-success' ?>">
                                                        <?= $medicine['stock_quantity'] ?> units
                                                    </span>
                                                    <?php if ($medicine['stock_quantity'] == 0): ?>
                                                        <span class="text-danger ms-2">Out of stock</span>
                                                    <?php elseif ($medicine['stock_quantity'] < 10): ?>
                                                        <span class="text-warning ms-2">Low stock</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="mb-3">Additional Details</h5>
                                        <table class="table table-borderless">
                                            <tr>
                                                <td class="detail-label">Price:</td>
                                                <td>
                                                    <strong>₹<?= number_format($medicine['price'], 2) ?></strong>
                                                    <?php if ($medicine['old_price'] > 0): ?>
                                                        <br><small class="text-muted text-decoration-line-through">
                                                            ₹<?= number_format($medicine['old_price'], 2) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="detail-label">Expiry Date:</td>
                                                <td>
                                                    <?php if ($medicine['expiry_date']): ?>
                                                        <?= date('M d, Y', strtotime($medicine['expiry_date'])) ?>
                                                        <?php 
                                                        $days_to_expiry = (strtotime($medicine['expiry_date']) - strtotime(date('Y-m-d'))) / 86400;
                                                        if ($days_to_expiry < 30): ?>
                                                            <span class="badge bg-warning ms-2">Expiring Soon</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not set</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="detail-label">Prescription:</td>
                                                <td>
                                                    <?php if ($medicine['prescription_required']): ?>
                                                        <span class="badge bg-warning">Required</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Not Required</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="detail-label">Status:</td>
                                                <td>
                                                    <span class="badge <?= $medicine['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                        <?= ucfirst($medicine['status']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <?php if (!empty($medicine['image_url'])): ?>
                                <hr>
                                <div class="mb-3">
                                    <h6>Image URL</h6>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($medicine['image_url']) ?>" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('<?= htmlspecialchars($medicine['image_url']) ?>')">
                                            <i class="bi bi-clipboard"></i> Copy
                                        </button>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <hr>

                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Created Date</h6>
                                        <p><?= date('M d, Y h:i A', strtotime($medicine['created_at'])) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Last Updated</h6>
                                        <p><?= date('M d, Y h:i A', strtotime($medicine['updated_at'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card view-card">
                            <div class="card-header">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="edit_medicine.php?id=<?= $medicine_id ?>" class="btn btn-primary">
                                        <i class="bi bi-pencil"></i> Edit Medicine
                                    </a>
                                    <button type="button" class="btn btn-outline-success" 
                                            onclick="toggleStatus(<?= $medicine_id ?>, '<?= $medicine['status'] ?>')">
                                        <i class="bi bi-toggle-on"></i> 
                                        <?= $medicine['status'] == 'active' ? 'Deactivate' : 'Activate' ?> Medicine
                                    </button>
                                    <a href="delete_medicine.php?id=<?= $medicine_id ?>" 
                                       class="btn btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this medicine?')">
                                        <i class="bi bi-trash"></i> Delete Medicine
                                    </a>
                                    <hr>
                                    <a href="manage_medicines.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Back to List
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card view-card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">Stock Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <h2 class="<?= $medicine['stock_quantity'] < 10 ? 'text-warning' : 'text-success' ?>">
                                        <?= $medicine['stock_quantity'] ?>
                                    </h2>
                                    <small class="text-muted">Units Available</small>
                                </div>
                                
                                <?php if ($medicine['stock_quantity'] == 0): ?>
                                    <div class="alert alert-danger">
                                        <i class="bi bi-exclamation-triangle"></i> Out of Stock
                                    </div>
                                <?php elseif ($medicine['stock_quantity'] < 10): ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i> Low Stock Alert
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-success">
                                        <i class="bi bi-check-circle"></i> Stock Level Good
                                    </div>
                                <?php endif; ?>
                                
                                <a href="edit_medicine.php?id=<?= $medicine_id ?>" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-pencil"></i> Update Stock
                                </a>
                            </div>
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

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('URL copied to clipboard!');
            });
        }
    </script>
</body>
</html>
