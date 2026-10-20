<?php
require_once '../kk_pharma/after_login/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['user_name'] ?? 'User';

// Handle order actions
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'cancel_order':
            $order_id = $_POST['order_id'] ?? 0;
            $reason = $_POST['reason'] ?? '';
            if (cancelOrder($order_id, $reason)) {
                $_SESSION['success_msg'] = "Order cancelled successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to cancel order. Order may already be processed.";
            }
            header("Location: orders.php");
            exit();
            break;
            
        case 'reorder':
            $order_id = $_POST['order_id'] ?? 0;
            if (reorderItems($order_id)) {
                $_SESSION['success_msg'] = "Items added to cart successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to reorder items. Some items may be out of stock.";
            }
            header("Location: cart.php");
            exit();
            break;
    }
}

// Get orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$status_filter = $_GET['status'] ?? 'all';
$orders = getUserOrders($user_id, $status_filter, $per_page, $offset);
$total_orders = getUserOrdersCount($user_id, $status_filter);
$total_pages = ceil($total_orders / $per_page);

// Order management functions
function getUserOrders($user_id, $status = 'all', $limit = 10, $offset = 0) {
    $conn = getDBConnection();
    
    $sql = "SELECT o.*, COUNT(oi.id) as item_count,
                   GROUP_CONCAT(m.name SEPARATOR ', ') as item_names
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            LEFT JOIN medicines m ON oi.medicine_id = m.id
            WHERE o.user_id = ?";
    
    $params = [$user_id];
    $types = "i";
    
    if ($status !== 'all') {
        $sql .= " AND o.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    $sql .= " GROUP BY o.id ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    $orders = [];
    $result = $stmt->get_result();
    while ($order = $result->fetch_assoc()) {
        $orders[] = $order;
    }
    
    return $orders;
}

function getUserOrdersCount($user_id, $status = 'all') {
    $conn = getDBConnection();
    
    $sql = "SELECT COUNT(DISTINCT id) as count FROM orders WHERE user_id = ?";
    $params = [$user_id];
    $types = "i";
    
    if ($status !== 'all') {
        $sql .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc()['count'];
}

function getOrderItems($order_id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT oi.*, m.image_url FROM order_items oi LEFT JOIN medicines m ON oi.medicine_id = m.id WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    $items = [];
    $result = $stmt->get_result();
    while ($item = $result->fetch_assoc()) {
        $items[] = $item;
    }
    
    return $items;
}

function cancelOrder($order_id, $reason) {
    $conn = getDBConnection();
    
    // Check if order can be cancelled (only pending or confirmed orders)
    $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if (!$order || !in_array($order['status'], ['pending', 'confirmed'])) {
        return false;
    }
    
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled', cancel_reason = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $reason, $order_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        // Restore stock quantities
        $items = getOrderItems($order_id);
        foreach ($items as $item) {
            $stmt = $conn->prepare("UPDATE medicines SET stock_quantity = stock_quantity + ? WHERE id = ?");
            $stmt->bind_param("ii", $item['quantity'], $item['medicine_id']);
            $stmt->execute();
        }
        return true;
    }
    
    return false;
}

function reorderItems($order_id) {
    $conn = getDBConnection();
    
    // Get order items
    $items = getOrderItems($order_id);
    $success = true;
    
    foreach ($items as $item) {
        // Check if medicine is still available and in stock
        $stmt = $conn->prepare("SELECT stock_quantity FROM medicines WHERE id = ? AND status = 'active'");
        $stmt->bind_param("i", $item['medicine_id']);
        $stmt->execute();
        $medicine = $stmt->get_result()->fetch_assoc();
        
        if ($medicine && $medicine['stock_quantity'] >= $item['quantity']) {
            // Add to cart
            if (!addToCart($item['medicine_id'], $item['quantity'])) {
                $success = false;
            }
        } else {
            $success = false;
        }
    }
    
    return $success;
}

function getStatusColor($status) {
    switch ($status) {
        case 'delivered': return 'success';
        case 'shipped': return 'info';
        case 'processing': return 'warning';
        case 'packed': return 'primary';
        case 'confirmed': return 'info';
        case 'cancelled': return 'danger';
        case 'returned': return 'secondary';
        case 'pending': return 'secondary';
        default: return 'secondary';
    }
}

function getPaymentStatusColor($status) {
    switch ($status) {
        case 'paid': return 'success';
        case 'pending': return 'warning';
        case 'failed': return 'danger';
        case 'refunded': return 'info';
        default: return 'secondary';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | KK Pharma</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../css/dashboard.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../Home.php">
                <i class="bi bi-hospital me-2"></i>KK Pharma
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../Home.php">
                            <i class="bi bi-house me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($user_name); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="index.php">Dashboard</a></li>
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php">Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="bi bi-cart3 me-2"></i>Cart
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="orders.php">
                                <i class="bi bi-box-seam me-2"></i>Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="subscriptions.php">
                                <i class="bi bi-arrow-repeat me-2"></i>Subscriptions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="wishlist.php">
                                <i class="bi bi-heart me-2"></i>Wishlist
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="prescriptions.php">
                                <i class="bi bi-file-medical me-2"></i>Prescriptions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="bi bi-person me-2"></i>Profile
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Messages -->
                <?php if (isset($_SESSION['success_msg'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_msg'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i><?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Page Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-box-seam me-2"></i>Order History
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="../products.php" class="btn btn-sm btn-primary">
                                <i class="bi bi-shop me-1"></i>New Order
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-0">Filter by Status:</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="btn-group" role="group">
                                    <a href="?status=all" class="btn btn-sm <?php echo $status_filter === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">All (<?php echo getUserOrdersCount($user_id, 'all'); ?>)</a>
                                    <a href="?status=pending" class="btn btn-sm <?php echo $status_filter === 'pending' ? 'btn-primary' : 'btn-outline-primary'; ?>">Pending (<?php echo getUserOrdersCount($user_id, 'pending'); ?>)</a>
                                    <a href="?status=processing" class="btn btn-sm <?php echo $status_filter === 'processing' ? 'btn-primary' : 'btn-outline-primary'; ?>">Processing (<?php echo getUserOrdersCount($user_id, 'processing'); ?>)</a>
                                    <a href="?status=shipped" class="btn btn-sm <?php echo $status_filter === 'shipped' ? 'btn-primary' : 'btn-outline-primary'; ?>">Shipped (<?php echo getUserOrdersCount($user_id, 'shipped'); ?>)</a>
                                    <a href="?status=delivered" class="btn btn-sm <?php echo $status_filter === 'delivered' ? 'btn-primary' : 'btn-outline-primary'; ?>">Delivered (<?php echo getUserOrdersCount($user_id, 'delivered'); ?>)</a>
                                    <a href="?status=cancelled" class="btn btn-sm <?php echo $status_filter === 'cancelled' ? 'btn-primary' : 'btn-outline-primary'; ?>">Cancelled (<?php echo getUserOrdersCount($user_id, 'cancelled'); ?>)</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders List -->
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="card shadow mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($order['order_number']); ?></h6>
                                    <small class="text-muted"><?php echo formatDate($order['created_at'], 'd M Y, h:i A'); ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?php echo getStatusColor($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span>
                                    <br>
                                    <small class="text-muted"><?php echo getPaymentStatusColor($order['payment_status']); ?> Payment: <?php echo ucfirst($order['payment_status']); ?></small>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-2">
                                            <strong>Items:</strong> <?php echo $order['item_count']; ?> products
                                        </div>
                                        <div class="mb-2">
                                            <strong>Products:</strong> <?php echo htmlspecialchars(substr($order['item_names'], 0, 100)); ?><?php echo strlen($order['item_names']) > 100 ? '...' : ''; ?>
                                        </div>
                                        <?php if ($order['tracking_number']): ?>
                                            <div class="mb-2">
                                                <strong>Tracking:</strong> <?php echo htmlspecialchars($order['tracking_number']); ?>
                                                <a href="#" class="btn btn-sm btn-outline-primary ms-2">Track</a>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($order['estimated_delivery']): ?>
                                            <div class="mb-2">
                                                <strong>Estimated Delivery:</strong> <?php echo formatDate($order['estimated_delivery']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="mb-2">
                                            <strong>Total Amount:</strong><br>
                                            <span class="h5 text-primary"><?php echo formatPrice($order['total_amount']); ?></span>
                                        </div>
                                        <div class="btn-group-vertical w-100">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                                <i class="bi bi-eye"></i> View Details
                                            </button>
                                            <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="cancel_order">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Are you sure you want to cancel this order?')">
                                                        <i class="bi bi-x"></i> Cancel Order
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($order['status'] === 'delivered'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="reorder">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                                        <i class="bi bi-arrow-repeat"></i> Reorder
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Order pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?status=<?php echo $status_filter; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?status=<?php echo $status_filter; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?status=<?php echo $status_filter; ?>&page=<?php echo $page + 1; ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-box-seam"></i>
                        <h4>No Orders Found</h4>
                        <p class="text-muted">
                            <?php if ($status_filter === 'all'): ?>
                                You haven't placed any orders yet. Start shopping to see your orders here!
                            <?php else: ?>
                                No orders with status "<?php echo ucfirst($status_filter); ?>" found.
                            <?php endif; ?>
                        </p>
                        <a href="../products.php" class="btn btn-primary">
                            <i class="bi bi-shop me-2"></i>Start Shopping
                        </a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        function viewOrderDetails(orderId) {
            // Load order details via AJAX
            fetch(`get_order_details.php?id=${orderId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('orderDetailsContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('orderDetailsModal')).show();
                })
                .catch(error => {
                    console.error('Error loading order details:', error);
                    alert('Failed to load order details');
                });
        }
    </script>
</body>
</html>
