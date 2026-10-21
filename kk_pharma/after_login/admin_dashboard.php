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

// Get dashboard statistics
$total_medicines = $conn->query("SELECT COUNT(*) as count FROM medicines")->fetch_assoc()['count'];
$active_medicines = $conn->query("SELECT COUNT(*) as count FROM medicines WHERE status = 'active'")->fetch_assoc()['count'];
$out_of_stock = $conn->query("SELECT COUNT(*) as count FROM medicines WHERE stock_quantity = 0")->fetch_assoc()['count'];
$low_stock = $conn->query("SELECT COUNT(*) as count FROM medicines WHERE stock_quantity < 10")->fetch_assoc()['count'];

// Get recent medicines
$recent_medicines = $conn->query("SELECT * FROM medicines ORDER BY created_at DESC LIMIT 5");

// Get low stock medicines
$low_stock_medicines = $conn->query("SELECT * FROM medicines WHERE stock_quantity < 10 ORDER BY stock_quantity ASC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PharmaCare</title>
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
        .stat-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        .table-actions {
            white-space: nowrap;
        }
        .badge-stock {
            font-size: 0.75rem;
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
                            <a class="nav-link active" href="admin_dashboard.php">
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
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                        <div class="text-muted">
                            Welcome, <strong><?= $_SESSION['admin_username'] ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-primary">
                                        <i class="bi bi-capsules"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="text-muted mb-1">Total Medicines</h6>
                                        <h3 class="mb-0"><?= $total_medicines ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-success">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="text-muted mb-1">Active Medicines</h6>
                                        <h3 class="mb-0"><?= $active_medicines ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-warning">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="text-muted mb-1">Low Stock</h6>
                                        <h3 class="mb-0"><?= $low_stock ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-danger">
                                        <i class="bi bi-x-circle"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="text-muted mb-1">Out of Stock</h6>
                                        <h3 class="mb-0"><?= $out_of_stock ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Medicines -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Medicines</h5>
                                <a href="manage_medicines.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Category</th>
                                                <th>Price</th>
                                                <th>Stock</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($medicine = $recent_medicines->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($medicine['name']) ?></td>
                                                <td><?= htmlspecialchars($medicine['category']) ?></td>
                                                <td>₹<?= number_format($medicine['price'], 2) ?></td>
                                                <td>
                                                    <span class="badge badge-stock <?= $medicine['stock_quantity'] < 10 ? 'bg-warning' : 'bg-success' ?>">
                                                        <?= $medicine['stock_quantity'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $medicine['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                        <?= ucfirst($medicine['status']) ?>
                                                    </span>
                                                </td>
                                                <td class="table-actions">
                                                    <a href="edit_medicine.php?id=<?= $medicine['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="delete_medicine.php?id=<?= $medicine['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Are you sure?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Low Stock Alert</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($low_stock_medicines->num_rows > 0): ?>
                                    <?php while ($medicine = $low_stock_medicines->fetch_assoc()): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 border rounded">
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($medicine['name']) ?></h6>
                                            <small class="text-muted">Stock: <?= $medicine['stock_quantity'] ?></small>
                                        </div>
                                        <a href="edit_medicine.php?id=<?= $medicine['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center">No low stock items</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
