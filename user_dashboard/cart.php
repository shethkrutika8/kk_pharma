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

// Handle cart actions
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update_cart':
            $medicine_id = $_POST['medicine_id'] ?? 0;
            $quantity = $_POST['quantity'] ?? 1;
            if (updateCartQuantity($medicine_id, $quantity)) {
                $_SESSION['success_msg'] = "Cart updated successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to update cart. Please check stock availability.";
            }
            header("Location: cart.php");
            exit();
            break;
            
        case 'remove_from_cart':
            $medicine_id = $_POST['medicine_id'] ?? 0;
            removeFromCart($medicine_id);
            $_SESSION['success_msg'] = "Item removed from cart!";
            header("Location: cart.php");
            exit();
            break;
            
        case 'clear_cart':
            clearCart();
            $_SESSION['success_msg'] = "Cart cleared successfully!";
            header("Location: cart.php");
            exit();
            break;
            
        case 'apply_coupon':
            $coupon_code = $_POST['coupon_code'] ?? '';
            if (applyCoupon($coupon_code, $cart_total)) {
                $_SESSION['success_msg'] = "Coupon applied successfully!";
            } else {
                $_SESSION['error_msg'] = "Invalid or expired coupon code.";
            }
            header("Location: cart.php");
            exit();
            break;
    }
}

// Get cart items and totals
$cart_items = getCartItems();
$cart_total = getCartTotal();
$cart_count = getCartCount();

// Calculate delivery charges and totals
$delivery_charge = ($cart_total >= 500) ? 0 : 40;
$subtotal = $cart_total;
$discount = 0; // Will be calculated based on coupons
$grand_total = $subtotal - $discount + $delivery_charge;

// Coupon function
function applyCoupon($coupon_code, $cart_total) {
    // This is a placeholder for coupon logic
    // In a real implementation, you would check against a coupons table
    if ($coupon_code === 'FIRST10' && $cart_total >= 200) {
        return true;
    }
    return false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | KK Pharma</title>
    
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
                            <a class="nav-link active" href="cart.php">
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

                <!-- Page Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-cart3 me-2"></i>Shopping Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="badge bg-secondary"><?php echo $cart_count; ?> items</span>
                        <?php endif; ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="../products.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-shop me-1"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (!empty($cart_items)): ?>
                    <div class="row">
                        <!-- Cart Items -->
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold">Cart Items</h6>
                                    <?php if ($cart_count > 0): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="clear_cart">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to clear your cart?')">
                                                <i class="bi bi-trash"></i> Clear Cart
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($cart_items as $item): ?>
                                        <div class="cart-item">
                                            <div class="row align-items-center">
                                                <div class="col-md-2">
                                                    <?php if ($item['image_url']): ?>
                                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid rounded">
                                                    <?php else: ?>
                                                        <img src="https://via.placeholder.com/80x80/00d1b2/ffffff?text=M" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid rounded">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                    <p class="text-muted small mb-1"><?php echo htmlspecialchars($item['generic_name'] ?? ''); ?></p>
                                                    <p class="text-muted small mb-0">Manufacturer: <?php echo htmlspecialchars($item['manufacturer'] ?? 'N/A'); ?></p>
                                                    <?php if ($item['prescription_required']): ?>
                                                        <span class="badge bg-warning text-dark mt-1">
                                                            <i class="bi bi-file-medical"></i> Prescription Required
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mb-2">
                                                        <span class="text-muted small">Price:</span><br>
                                                        <span class="fw-bold price"><?php echo formatPrice($item['price']); ?></span>
                                                    </div>
                                                    <div class="text-muted small">
                                                        Stock: <?php echo $item['stock_quantity']; ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <form method="POST" class="d-flex align-items-center">
                                                        <input type="hidden" name="action" value="update_cart">
                                                        <input type="hidden" name="medicine_id" value="<?php echo $item['id']; ?>">
                                                        <div class="input-group input-group-sm">
                                                            <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">
                                                                <i class="bi bi-dash"></i>
                                                            </button>
                                                            <input type="number" name="quantity" value="<?php echo $item['cart_quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" class="form-control text-center" id="qty-<?php echo $item['id']; ?>">
                                                            <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">
                                                                <i class="bi bi-plus"></i>
                                                            </button>
                                                        </div>
                                                        <button type="submit" class="btn btn-sm btn-primary ms-2" style="display: none;">Update</button>
                                                    </form>
                                                </div>
                                                <div class="col-md-1 text-end">
                                                    <div class="fw-bold"><?php echo formatPrice($item['subtotal']); ?></div>
                                                </div>
                                                <div class="col-md-1 text-end">
                                                    <form method="POST">
                                                        <input type="hidden" name="action" value="remove_from_cart">
                                                        <input type="hidden" name="medicine_id" value="<?php echo $item['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove this item from cart?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Coupon Section -->
                            <div class="card shadow mb-4">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold">Have a Coupon?</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" class="d-flex">
                                        <input type="hidden" name="action" value="apply_coupon">
                                        <input type="text" name="coupon_code" class="form-control me-2" placeholder="Enter coupon code" required>
                                        <button type="submit" class="btn btn-primary">Apply Coupon</button>
                                    </form>
                                    <small class="text-muted">Try: FIRST10 for 10% off on orders above ₹200</small>
                                </div>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold">Order Summary</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal (<?php echo $cart_count; ?> items):</span>
                                        <strong><?php echo formatPrice($subtotal); ?></strong>
                                    </div>
                                    <?php if ($discount > 0): ?>
                                        <div class="d-flex justify-content-between mb-2 text-success">
                                            <span>Discount:</span>
                                            <strong>-<?php echo formatPrice($discount); ?></strong>
                                        </div>
                                    <?php endif; ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Delivery Charge:</span>
                                        <strong>
                                            <?php if ($delivery_charge == 0): ?>
                                                <span class="text-success">FREE</span>
                                            <?php else: ?>
                                                <?php echo formatPrice($delivery_charge); ?>
                                            <?php endif; ?>
                                        </strong>
                                    </div>
                                    <?php if ($delivery_charge > 0): ?>
                                        <small class="text-muted mb-2 d-block">Free delivery on orders above ₹500</small>
                                    <?php endif; ?>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-3">
                                        <h5>Total:</h5>
                                        <h5><?php echo formatPrice($grand_total); ?></h5>
                                    </div>
                                    
                                    <button class="btn btn-primary w-100 mb-2" onclick="proceedToCheckout()">
                                        <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                                    </button>
                                    
                                    <a href="../products.php" class="btn btn-outline-secondary w-100">
                                        <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                                    </a>
                                </div>
                            </div>

                            <!-- Delivery Info -->
                            <div class="card shadow">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="bi bi-truck me-2"></i>Delivery Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <span>Standard Delivery (3-5 days)</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <span>Cash on Delivery Available</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <span>Secure Packaging</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Empty Cart -->
                    <div class="empty-state">
                        <i class="bi bi-cart-x"></i>
                        <h4>Your cart is empty</h4>
                        <p class="text-muted">Looks like you haven't added any products to your cart yet.</p>
                        <a href="../products.php" class="btn btn-primary">
                            <i class="bi bi-shop me-2"></i>Start Shopping
                        </a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        function updateQuantity(medicineId, change) {
            const input = document.getElementById('qty-' + medicineId);
            const currentValue = parseInt(input.value);
            const maxValue = parseInt(input.max);
            const newValue = currentValue + change;
            
            if (newValue >= 1 && newValue <= maxValue) {
                input.value = newValue;
                // Auto-submit the form
                input.form.submit();
            }
        }

        function proceedToCheckout() {
            // Check if any prescription items are in cart
            const hasPrescriptionItems = <?php echo !empty($cart_items) && array_filter($cart_items, fn($item) => $item['prescription_required']) ? 'true' : 'false'; ?>;
            
            if (hasPrescriptionItems) {
                if (confirm('You have prescription-required items in your cart. You will need to upload a valid prescription during checkout. Continue?')) {
                    window.location.href = 'checkout.php';
                }
            } else {
                window.location.href = 'checkout.php';
            }
        }

        // Auto-update quantity when input changes
        document.querySelectorAll('input[name="quantity"]').forEach(input => {
            input.addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
</body>
</html>
