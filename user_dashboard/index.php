<?php
require_once '../kk_pharma/after_login/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['email'] ?? '';

// Validate user ID exists
if (!$user_id || !is_numeric($user_id) || $user_id <= 0) {
    $_SESSION['error_msg'] = "Invalid user session. Please login again.";
    header("Location: ../login.php");
    exit();
}

// Handle actions
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_to_cart':
            $medicine_id = $_POST['medicine_id'] ?? 0;
            $quantity = $_POST['quantity'] ?? 1;
            if (addToCart($medicine_id, $quantity)) {
                $_SESSION['success_msg'] = "Product added to cart successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to add product to cart. Please check stock availability.";
            }
            header("Location: index.php");
            exit();
            break;
            
        case 'add_to_wishlist':
            $medicine_id = $_POST['medicine_id'] ?? 0;
            if (addToWishlist($user_id, $medicine_id)) {
                $_SESSION['success_msg'] = "Product added to wishlist!";
            } else {
                $_SESSION['error_msg'] = "Failed to add to wishlist.";
            }
            header("Location: index.php");
            exit();
            break;
            
        case 'subscribe':
            $medicine_id = $_POST['medicine_id'] ?? 0;
            $subscription_type = $_POST['subscription_type'] ?? 'monthly';
            $quantity = $_POST['quantity'] ?? 1;
            if (createSubscription($user_id, $medicine_id, $subscription_type, $quantity)) {
                $_SESSION['success_msg'] = "Subscription created successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to create subscription.";
            }
            header("Location: index.php");
            exit();
            break;
    }
}

// Get dashboard statistics
$dashboard_stats = getUserDashboardStats($user_id);

// Get cart items and total
$cart_items = getCartItems();
$cart_total = getCartTotal();
$cart_count = getCartCount();

// Get featured medicines for display
$medicines_result = getFeaturedMedicines(8);
$medicines = [];
while ($medicine = $medicines_result->fetch_assoc()) {
    $medicines[] = $medicine;
}

// Get recent orders
$recent_orders = getUserRecentOrders($user_id, 5);

// Get active subscriptions
$subscriptions = getUserActiveSubscriptions($user_id, 3);

// Get wishlist items
$wishlist_items = getUserWishlist($user_id, 4);

// Extended functions for dashboard
function getUserDashboardStats($user_id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT c.id) as cart_items, COUNT(DISTINCT s.id) as active_subscriptions, COUNT(DISTINCT w.id) as wishlist_items, COUNT(DISTINCT o.id) as total_orders, COALESCE(SUM(CASE WHEN o.status = 'delivered' THEN o.total_amount ELSE 0 END), 0) as total_spent FROM register u LEFT JOIN cart c ON u.id = c.user_id LEFT JOIN subscriptions s ON u.id = s.user_id AND s.status = 'active' LEFT JOIN wishlist w ON u.id = w.user_id LEFT JOIN orders o ON u.id = o.user_id WHERE u.id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function getFeaturedMedicines($limit = 8) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT m.*, c.name as category_name FROM medicines m LEFT JOIN categories c ON m.category_id = c.id WHERE m.status = 'active' AND (m.is_featured = 1 OR m.rating >= 4.0) ORDER BY m.rating DESC, m.review_count DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    
    return $stmt->get_result();
}

function getUserRecentOrders($user_id, $limit = 5) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id WHERE o.user_id = ? GROUP BY o.id ORDER BY o.created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    
    $orders = [];
    $result = $stmt->get_result();
    while ($order = $result->fetch_assoc()) {
        $orders[] = $order;
    }
    
    return $orders;
}

function getUserActiveSubscriptions($user_id, $limit = 3) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT s.*, m.name as medicine_name, m.image_url, DATEDIFF(s.next_delivery_date, CURDATE()) as days_until_delivery FROM subscriptions s JOIN medicines m ON s.medicine_id = m.id WHERE s.user_id = ? AND s.status = 'active' ORDER BY s.next_delivery_date ASC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    
    $subscriptions = [];
    $result = $stmt->get_result();
    while ($subscription = $result->fetch_assoc()) {
        $subscriptions[] = $subscription;
    }
    
    return $subscriptions;
}

function addToWishlist($user_id, $medicine_id) {
    $conn = getDBConnection();
    
    // Check if already in wishlist
    $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND medicine_id = ?");
    $stmt->bind_param("ii", $user_id, $medicine_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return false; // Already in wishlist
    }
    
    // Add to wishlist
    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, medicine_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $medicine_id);
    
    return $stmt->execute();
}

function getUserWishlist($user_id, $limit = 10) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT w.*, m.name, m.price, m.image_url, m.rating FROM wishlist w JOIN medicines m ON w.medicine_id = m.id WHERE w.user_id = ? ORDER BY w.created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    
    $wishlist = [];
    $result = $stmt->get_result();
    while ($item = $result->fetch_assoc()) {
        $wishlist[] = $item;
    }
    
    return $wishlist;
}

function createSubscription($user_id, $medicine_id, $subscription_type, $quantity) {
    $conn = getDBConnection();
    $medicine = getMedicineById($medicine_id);
    
    if (!$medicine) {
        return false;
    }
    
    $price = $medicine['price'];
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime('+1 year'));
    
    // Calculate next delivery date based on subscription type
    switch ($subscription_type) {
        case 'weekly':
            $next_delivery = date('Y-m-d', strtotime('+1 week'));
            break;
        case 'monthly':
            $next_delivery = date('Y-m-d', strtotime('+1 month'));
            break;
        case 'quarterly':
            $next_delivery = date('Y-m-d', strtotime('+3 months'));
            break;
        case 'yearly':
            $next_delivery = date('Y-m-d', strtotime('+1 year'));
            break;
        default:
            $next_delivery = date('Y-m-d', strtotime('+1 month'));
    }
    
    $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, medicine_id, subscription_type, quantity, price, start_date, end_date, next_delivery_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisissss", $user_id, $medicine_id, $subscription_type, $quantity, $price, $start_date, $end_date, $next_delivery);
    
    return $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | KK Pharma</title>
    
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
                            <a class="nav-link active" href="index.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="bi bi-cart3 me-2"></i>Cart
                                <?php if ($cart_count > 0): ?>
                                    <span class="badge bg-danger"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
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

                <!-- Welcome Section -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Welcome back, <?php echo htmlspecialchars($user_name); ?>! 👋</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="../products.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-shop me-1"></i>Browse Products
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Cart Items
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $dashboard_stats['cart_items']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-cart3 fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Active Subscriptions
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $dashboard_stats['active_subscriptions']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-arrow-repeat fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Wishlist Items
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $dashboard_stats['wishlist_items']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-heart fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Total Orders
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $dashboard_stats['total_orders']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-box-seam fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions & Featured Products -->
                <div class="row">
                    <!-- Featured Products -->
                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Featured Products</h6>
                                <a href="../products.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($medicines as $medicine): ?>
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card h-100 product-card">
                                                <?php if ($medicine['image_url']): ?>
                                                    <img src="<?php echo htmlspecialchars($medicine['image_url']); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($medicine['name']); ?>">
                                                <?php else: ?>
                                                    <img src="https://via.placeholder.com/200x150/00d1b2/ffffff?text=Medicine" class="card-img-top product-image" alt="<?php echo htmlspecialchars($medicine['name']); ?>">
                                                <?php endif; ?>
                                                
                                                <div class="card-body">
                                                    <h6 class="card-title"><?php echo htmlspecialchars($medicine['name']); ?></h6>
                                                    <p class="card-text small text-muted"><?php echo htmlspecialchars(substr($medicine['description'] ?? '', 0, 60)); ?>...</p>
                                                    
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div>
                                                            <?php if ($medicine['discount_price']): ?>
                                                                <span class="text-muted text-decoration-line-through small"><?php echo formatPrice($medicine['price']); ?></span>
                                                                <span class="fw-bold text-success"><?php echo formatPrice($medicine['discount_price']); ?></span>
                                                            <?php else: ?>
                                                                <span class="fw-bold text-primary"><?php echo formatPrice($medicine['price']); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="small text-warning">
                                                            <i class="bi bi-star-fill"></i> <?php echo number_format($medicine['rating'], 1); ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="d-flex gap-1">
                                                        <form method="POST" class="flex-grow-1">
                                                            <input type="hidden" name="action" value="add_to_cart">
                                                            <input type="hidden" name="medicine_id" value="<?php echo $medicine['id']; ?>">
                                                            <input type="hidden" name="quantity" value="1">
                                                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                                                <i class="bi bi-cart-plus"></i> Cart
                                                            </button>
                                                        </form>
                                                        
                                                        <form method="POST">
                                                            <input type="hidden" name="action" value="add_to_wishlist">
                                                            <input type="hidden" name="medicine_id" value="<?php echo $medicine['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="bi bi-heart"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="col-lg-4">
                        <!-- Recent Orders -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($recent_orders)): ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($order['order_number']); ?></h6>
                                                <small class="text-muted"><?php echo formatDate($order['created_at'], 'd M Y'); ?></small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?php echo getStatusColor($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span>
                                                <div class="small fw-bold"><?php echo formatPrice($order['total_amount']); ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="text-center mt-3">
                                        <a href="orders.php" class="btn btn-sm btn-primary">View All Orders</a>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center">No orders yet</p>
                                    <div class="text-center">
                                        <a href="../products.php" class="btn btn-sm btn-primary">Start Shopping</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Active Subscriptions -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Active Subscriptions</h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($subscriptions)): ?>
                                    <?php foreach ($subscriptions as $subscription): ?>
                                        <div class="mb-3 pb-2 border-bottom">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($subscription['medicine_name']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo ucfirst($subscription['subscription_type']); ?> • Qty: <?php echo $subscription['quantity']; ?>
                                            </small>
                                            <div class="small mt-1">
                                                <?php if ($subscription['days_until_delivery'] <= 0): ?>
                                                    <span class="text-danger">Delivery due!</span>
                                                <?php elseif ($subscription['days_until_delivery'] <= 3): ?>
                                                    <span class="text-warning">Due in <?php echo $subscription['days_until_delivery']; ?> days</span>
                                                <?php else: ?>
                                                    <span class="text-success">Due in <?php echo $subscription['days_until_delivery']; ?> days</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="text-center mt-3">
                                        <a href="subscriptions.php" class="btn btn-sm btn-primary">Manage Subscriptions</a>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center">No active subscriptions</p>
                                    <div class="text-center">
                                        <a href="../products.php" class="btn btn-sm btn-primary">Subscribe to Products</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../js/dashboard.js"></script>
</body>
</html>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'delivered': return 'success';
        case 'shipped': return 'info';
        case 'processing': return 'warning';
        case 'cancelled': return 'danger';
        case 'pending': return 'secondary';
        default: return 'secondary';
    }
}
?>
