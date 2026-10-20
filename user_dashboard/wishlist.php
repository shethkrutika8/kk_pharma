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

// Handle wishlist actions
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'remove_from_wishlist':
            $medicine_id = $_POST['medicine_id'] ?? 0;
            if (removeFromWishlist($user_id, $medicine_id)) {
                $_SESSION['success_msg'] = "Item removed from wishlist!";
            } else {
                $_SESSION['error_msg'] = "Failed to remove item from wishlist.";
            }
            header("Location: wishlist.php");
            exit();
            break;
            
        case 'add_to_cart_from_wishlist':
            $medicine_id = $_POST['medicine_id'] ?? 0;
            $quantity = $_POST['quantity'] ?? 1;
            if (addToCart($medicine_id, $quantity)) {
                $_SESSION['success_msg'] = "Item added to cart successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to add to cart. Please check stock availability.";
            }
            header("Location: wishlist.php");
            exit();
            break;
            
        case 'move_all_to_cart':
            $medicine_ids = $_POST['medicine_ids'] ?? [];
            $success_count = 0;
            $error_count = 0;
            
            foreach ($medicine_ids as $medicine_id) {
                if (addToCart($medicine_id, 1)) {
                    $success_count++;
                    removeFromWishlist($user_id, $medicine_id);
                } else {
                    $error_count++;
                }
            }
            
            if ($success_count > 0) {
                $_SESSION['success_msg'] = "$success_count items added to cart successfully!";
            }
            if ($error_count > 0) {
                $_SESSION['error_msg'] = "$error_count items could not be added to cart.";
            }
            header("Location: wishlist.php");
            exit();
            break;
            
        case 'clear_wishlist':
            if (clearWishlist($user_id)) {
                $_SESSION['success_msg'] = "Wishlist cleared successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to clear wishlist.";
            }
            header("Location: wishlist.php");
            exit();
            break;
    }
}

// Get wishlist items
$wishlist_items = getUserWishlist($user_id);

// Wishlist functions
function getUserWishlist($user_id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT w.*, m.name, m.description, m.price, m.discount_price, m.image_url, m.rating, m.review_count, m.stock_quantity, m.manufacturer, m.prescription_required FROM wishlist w JOIN medicines m ON w.medicine_id = m.id WHERE w.user_id = ? ORDER BY w.created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $wishlist = [];
    $result = $stmt->get_result();
    while ($item = $result->fetch_assoc()) {
        $wishlist[] = $item;
    }
    
    return $wishlist;
}

function removeFromWishlist($user_id, $medicine_id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND medicine_id = ?");
    $stmt->bind_param("ii", $user_id, $medicine_id);
    
    return $stmt->execute();
}

function clearWishlist($user_id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    
    return $stmt->execute();
}

function getWishlistCount($user_id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc()['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist | KK Pharma</title>
    
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
                            <a class="nav-link active" href="wishlist.php">
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
                        <i class="bi bi-heart me-2"></i>My Wishlist
                        <?php if (!empty($wishlist_items)): ?>
                            <span class="badge bg-secondary"><?php echo count($wishlist_items); ?> items</span>
                        <?php endif; ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <?php if (!empty($wishlist_items)): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="clear_wishlist">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to clear your entire wishlist?')">
                                        <i class="bi bi-trash"></i> Clear Wishlist
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="../products.php" class="btn btn-sm btn-primary">
                                <i class="bi bi-shop me-1"></i>Browse Products
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (!empty($wishlist_items)): ?>
                    <!-- Wishlist Actions Bar -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                        <label class="form-check-label" for="selectAll">
                                            Select all items
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <form method="POST" id="bulkActionsForm" style="display: inline;">
                                        <input type="hidden" name="action" value="move_all_to_cart">
                                        <input type="hidden" name="medicine_ids" id="selectedMedicines">
                                        <button type="submit" class="btn btn-success" id="bulkAddToCart" disabled>
                                            <i class="bi bi-cart-plus me-1"></i>Add Selected to Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Wishlist Items Grid -->
                    <div class="row">
                        <?php foreach ($wishlist_items as $item): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card shadow h-100 wishlist-item">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input medicine-checkbox" type="checkbox" value="<?php echo $item['medicine_id']; ?>">
                                            </div>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="remove_from_wishlist">
                                                <input type="hidden" name="medicine_id" value="<?php echo $item['medicine_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <div class="text-center mb-3">
                                            <?php if ($item['image_url']): ?>
                                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid rounded" style="max-height: 150px; object-fit: cover;">
                                            <?php else: ?>
                                                <img src="https://via.placeholder.com/200x150/00d1b2/ffffff?text=Medicine" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid rounded" style="max-height: 150px; object-fit: cover;">
                                            <?php endif; ?>
                                        </div>

                                        <h6 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <p class="card-text small text-muted">
                                            <?php echo htmlspecialchars(substr($item['description'] ?? '', 0, 80)); ?>...
                                        </p>

                                        <div class="mb-2">
                                            <small class="text-muted">Manufacturer:</small> <?php echo htmlspecialchars($item['manufacturer'] ?? 'N/A'); ?>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <?php if ($item['discount_price']): ?>
                                                    <span class="text-muted text-decoration-line-through small"><?php echo formatPrice($item['price']); ?></span>
                                                    <span class="fw-bold text-success"><?php echo formatPrice($item['discount_price']); ?></span>
                                                <?php else: ?>
                                                    <span class="fw-bold price"><?php echo formatPrice($item['price']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="small text-warning">
                                                <i class="bi bi-star-fill"></i> <?php echo number_format($item['rating'], 1); ?>
                                                <span class="text-muted">(<?php echo $item['review_count']; ?>)</span>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted">Stock: </small>
                                            <?php if ($item['stock_quantity'] > 0): ?>
                                                <span class="text-success"><?php echo $item['stock_quantity']; ?> available</span>
                                            <?php else: ?>
                                                <span class="text-danger">Out of stock</span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($item['prescription_required']): ?>
                                            <div class="mb-2">
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-file-medical"></i> Prescription Required
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="d-flex gap-2">
                                            <form method="POST" class="flex-grow-1">
                                                <input type="hidden" name="action" value="add_to_cart_from_wishlist">
                                                <input type="hidden" name="medicine_id" value="<?php echo $item['medicine_id']; ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-sm btn-primary w-100" <?php echo $item['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                                </button>
                                            </form>
                                            
                                            <form method="POST">
                                                <input type="hidden" name="action" value="subscribe">
                                                <input type="hidden" name="medicine_id" value="<?php echo $item['medicine_id']; ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <input type="hidden" name="subscription_type" value="monthly">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Empty Wishlist -->
                    <div class="empty-state">
                        <i class="bi bi-heart"></i>
                        <h4>Your wishlist is empty</h4>
                        <p class="text-muted">Save your favorite medicines here so you can easily find them later.</p>
                        <div class="mt-3">
                            <a href="../products.php" class="btn btn-primary me-2">
                                <i class="bi bi-shop me-2"></i>Browse Products
                            </a>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Wishlist Tips -->
                <?php if (!empty($wishlist_items)): ?>
                    <div class="card shadow mt-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Wishlist Tips</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <span>Items stay in your wishlist until you remove them</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <span>Get notified when wishlist items go on sale</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <span>Quickly add multiple items to cart</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Select all functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.medicine-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });

        // Update bulk actions button state
        function updateBulkActions() {
            const checkedBoxes = document.querySelectorAll('.medicine-checkbox:checked');
            const bulkButton = document.getElementById('bulkAddToCart');
            const selectedMedicines = document.getElementById('selectedMedicines');
            
            if (checkedBoxes.length > 0) {
                bulkButton.disabled = false;
                const medicineIds = Array.from(checkedBoxes).map(cb => cb.value);
                selectedMedicines.value = JSON.stringify(medicineIds);
            } else {
                bulkButton.disabled = true;
                selectedMedicines.value = '';
            }
        }

        // Add event listeners to checkboxes
        document.querySelectorAll('.medicine-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActions);
        });

        // Bulk add to cart form submission
        document.getElementById('bulkActionsForm').addEventListener('submit', function(e) {
            const checkedBoxes = document.querySelectorAll('.medicine-checkbox:checked');
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('Please select at least one item to add to cart');
                return;
            }
            
            if (confirm(`Add ${checkedBoxes.length} items to your cart?`)) {
                // Convert JSON array to form data
                const selectedMedicines = document.getElementById('selectedMedicines');
                const medicineIds = JSON.parse(selectedMedicines.value);
                
                // Clear the JSON input and add individual hidden inputs
                selectedMedicines.remove();
                
                medicineIds.forEach(medicineId => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'medicine_ids[]';
                    input.value = medicineId;
                    this.appendChild(input);
                });
            } else {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
