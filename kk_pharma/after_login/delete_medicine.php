<?php
require_once 'config.php';
requireAdminLogin();

$medicine_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($medicine_id <= 0) {
    header("Location: manage_medicines.php");
    exit();
}

// Get medicine details for confirmation
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

// Handle deletion
if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
    $delete_sql = "DELETE FROM medicines WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $medicine_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success'] = "Medicine '{$medicine['name']}' has been deleted successfully.";
        header("Location: manage_medicines.php");
        exit();
    } else {
        $error = "Error deleting medicine: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Medicine - PharmaCare Admin</title>
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
        .delete-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #dc3545;
        }
        .medicine-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .danger-zone {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 8px;
            padding: 20px;
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
                    <h1 class="h2">Delete Medicine</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="manage_medicines.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Medicines
                        </a>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <?= displayAlert($error, 'danger') ?>
                <?php endif; ?>

                <div class="card delete-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="text-danger me-3">
                                <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                            </div>
                            <div>
                                <h4 class="mb-1">Delete Medicine Confirmation</h4>
                                <p class="text-muted mb-0">This action cannot be undone. Please confirm the medicine details below.</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <h5>Medicine Details</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="150"><strong>Name:</strong></td>
                                        <td><?= htmlspecialchars($medicine['name']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Category:</strong></td>
                                        <td><?= htmlspecialchars($medicine['category']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Price:</strong></td>
                                        <td>₹<?= number_format($medicine['price'], 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Stock:</strong></td>
                                        <td><?= $medicine['stock_quantity'] ?> units</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Manufacturer:</strong></td>
                                        <td><?= htmlspecialchars($medicine['manufacturer']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Description:</strong></td>
                                        <td><?= !empty($medicine['description']) ? htmlspecialchars($medicine['description']) : '<span class="text-muted">No description</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span class="badge <?= $medicine['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= ucfirst($medicine['status']) ?>
                                            </span>
                                            <?php if ($medicine['prescription_required']): ?>
                                                <span class="badge bg-warning ms-1">Rx Required</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-4 text-center">
                                <?php if (!empty($medicine['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($medicine['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($medicine['name']) ?>" 
                                         class="medicine-image mb-3">
                                <?php else: ?>
                                    <div class="medicine-image bg-light d-flex align-items-center justify-content-center mx-auto mb-3">
                                        <i class="bi bi-capsules fs-3 text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <h6><?= htmlspecialchars($medicine['name']) ?></h6>
                                <p class="text-muted small">ID: #<?= str_pad($medicine['id'], 5, '0', STR_PAD_LEFT) ?></p>
                            </div>
                        </div>

                        <div class="danger-zone mt-4">
                            <h6 class="text-danger mb-3">
                                <i class="bi bi-exclamation-triangle"></i> Warning
                            </h6>
                            <p class="mb-3">Deleting this medicine will:</p>
                            <ul class="mb-0">
                                <li>Permanently remove the medicine from the database</li>
                                <li>Delete all associated data and history</li>
                                <li>Remove the medicine from user carts and wishlists</li>
                                <li>This action cannot be reversed</li>
                            </ul>
                        </div>

                        <form method="POST" class="mt-4">
                            <input type="hidden" name="confirm_delete" value="yes">
                            
                            <div class="d-flex justify-content-between">
                                <a href="edit_medicine.php?id=<?= $medicine_id ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit Instead
                                </a>
                                
                                <div>
                                    <a href="manage_medicines.php" class="btn btn-secondary me-2">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-trash"></i> Delete Medicine
                                    </button>
                                </div>
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
