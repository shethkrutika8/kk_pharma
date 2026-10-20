<?php
require_once 'kk_pharma/after_login/config.php';

// Get product ID
$medicine_id = (int)($_GET['id'] ?? 0);
if ($medicine_id === 0) {
    header("Location: products.php");
    exit();
}

// Get medicine details
$medicine = getMedicineById($medicine_id);
if (!$medicine) {
    header("Location: products.php");
    exit();
}

// Get related products
// Check if status column exists
$status_check = $conn->query("SHOW COLUMNS FROM medicines LIKE 'status'");
if ($status_check->num_rows > 0) {
    $related_result = $conn->prepare("SELECT * FROM medicines WHERE category_id = ? AND id != ? AND (status = 'active' OR status IS NULL) ORDER BY RAND() LIMIT 8");
} else {
    $related_result = $conn->prepare("SELECT * FROM medicines WHERE category_id = ? AND id != ? ORDER BY RAND() LIMIT 8");
}
$related_result->bind_param("ii", $medicine['category_id'], $medicine_id);
$related_result->execute();
$related_products = [];
while ($product = $related_result->get_result()->fetch_assoc()) {
    $related_products[] = $product;
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_to_cart' && isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
        $quantity = (int)($_POST['quantity'] ?? 1);
        if (addToCart($medicine_id, $quantity)) {
            $_SESSION['success_msg'] = "Product added to cart successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to add product to cart. Please check stock availability.";
        }
        header("Location: product.php?id=" . $medicine_id);
        exit();
    }
}

// Get category name
$category_stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
$category_stmt->bind_param("i", $medicine['category_id']);
$category_stmt->execute();
$category_result = $category_stmt->get_result()->fetch_assoc();
$category_name = $category_result['name'] ?? 'Uncategorized';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($medicine['name']); ?> | KK Pharma</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/dashboard.css" rel="stylesheet">
    
    <style>
        .product-gallery {
            position: sticky;
            top: 20px;
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .thumbnail:hover,
        .thumbnail.active {
            border-color: var(--accent-color);
            transform: scale(1.05);
        }
        
        .product-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1rem;
        }
        
        .product-price {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .current-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent-color);
        }
        
        .original-price {
            font-size: 1.5rem;
            color: #6c757d;
            text-decoration: line-through;
        }
        
        .discount-badge {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .stock-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        
        .in-stock {
            color: #28a745;
        }
        
        .low-stock {
            color: #ffc107;
        }
        
        .out-of-stock {
            color: #dc3545;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            width: 150px;
        }
        
        .quantity-btn {
            background: #f8f9fa;
            border: none;
            padding: 0.75rem 1rem;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .quantity-btn:hover {
            background: #e9ecef;
        }
        
        .quantity-input {
            border: none;
            text-align: center;
            width: 50px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .btn-add-cart {
            background: var(--accent-color);
            border: none;
            color: white;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            flex: 1;
        }
        
        .btn-add-cart:hover:not(:disabled) {
            background: #00b894;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 209, 178, 0.3);
        }
        
        .btn-add-cart:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .btn-wishlist {
            background: white;
            border: 2px solid #e9ecef;
            color: #6c757d;
            padding: 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-wishlist:hover,
        .btn-wishlist.active {
            border-color: #dc3545;
            color: #dc3545;
            background: #fff5f5;
        }
        
        .product-features {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .feature-icon {
            width: 40px;
            height: 40px;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .info-tabs {
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 2rem;
        }
        
        .nav-tab {
            border: none;
            background: none;
            color: #6c757d;
            font-weight: 600;
            padding: 1rem 1.5rem;
            position: relative;
            transition: color 0.3s ease;
        }
        
        .nav-tab:hover {
            color: var(--primary-color);
        }
        
        .nav-tab.active {
            color: var(--accent-color);
        }
        
        .nav-tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--accent-color);
        }
        
        .related-product {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .related-product:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .related-image {
            height: 150px;
            object-fit: cover;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .prescription-alert {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .rating-display {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .stars {
            color: #ffc107;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="Home.php">
                <i class="bi bi-hospital me-2"></i>KK Pharma
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="Home.php">
                            <i class="bi bi-house me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="bi bi-shop me-1"></i>Products
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="user_dashboard/index.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="user_dashboard/cart.php">Cart</a></li>
                                <li><a class="dropdown-item" href="user_dashboard/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="Home.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php" class="text-decoration-none">Products</a></li>
                <li class="breadcrumb-item"><a href="products.php?category=<?php echo $medicine['category_id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($category_name); ?></a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($medicine['name']); ?></li>
            </ol>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="container py-4">
        <div class="row">
            <!-- Product Gallery -->
            <div class="col-lg-6">
                <div class="product-gallery">
                    <div class="main-image mb-3">
                        <?php if ($medicine['image_url']): ?>
                            <img id="mainImage" src="<?php echo htmlspecialchars($medicine['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($medicine['name']); ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div class="text-muted">
                                <i class="bi bi-image" style="font-size: 4rem;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($medicine['image_url']): ?>
                        <div class="d-flex gap-2">
                            <img src="<?php echo htmlspecialchars($medicine['image_url']); ?>" 
                                 class="thumbnail active"
                                 onclick="changeImage(this)"
                                 alt="Product view 1">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Product Details -->
            <div class="col-lg-6">
                <?php if ($medicine['prescription_required']): ?>
                    <div class="prescription-alert">
                        <i class="bi bi-file-medical me-2"></i>
                        <strong>Prescription Required:</strong> This medicine requires a valid prescription from a registered medical practitioner.
                    </div>
                <?php endif; ?>
                
                <h1 class="product-title"><?php echo htmlspecialchars($medicine['name']); ?></h1>
                
                <div class="rating-display">
                    <div class="stars">
                        <?php
                        $rating = round($medicine['rating'] ?? 0);
                        for ($i = 1; $i <= 5; $i++):
                            echo $i <= $rating ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                        endfor;
                        ?>
                    </div>
                    <span><?php echo number_format($medicine['rating'] ?? 0, 1); ?> (<?php echo $medicine['review_count'] ?? 0; ?> reviews)</span>
                </div>
                
                <div class="product-price">
                    <span class="current-price">₹<?php echo number_format($medicine['discount_price'] ?? $medicine['price'], 2); ?></span>
                    <?php if ($medicine['discount_price'] && $medicine['discount_price'] < $medicine['price']): ?>
                        <span class="original-price">₹<?php echo number_format($medicine['price'], 2); ?></span>
                        <span class="discount-badge">
                            <?php echo round((($medicine['price'] - $medicine['discount_price']) / $medicine['price']) * 100); ?>% OFF
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="stock-info">
                    <?php if ($medicine['stock_quantity'] > 10): ?>
                        <i class="bi bi-check-circle-fill in-stock"></i>
                        <span class="in-stock">In Stock (<?php echo $medicine['stock_quantity']; ?> units available)</span>
                    <?php elseif ($medicine['stock_quantity'] > 0): ?>
                        <i class="bi bi-exclamation-triangle-fill low-stock"></i>
                        <span class="low-stock">Only <?php echo $medicine['stock_quantity']; ?> units left</span>
                    <?php else: ?>
                        <i class="bi bi-x-circle-fill out-of-stock"></i>
                        <span class="out-of-stock">Out of Stock</span>
                    <?php endif; ?>
                </div>
                
                <div class="product-features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div>
                            <strong>Free Delivery</strong><br>
                            <small class="text-muted">On orders above ₹500</small>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <strong>Authentic Products</strong><br>
                            <small class="text-muted">100% genuine medicines</small>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                        <div>
                            <strong>Easy Returns</strong><br>
                            <small class="text-muted">7-day return policy</small>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                    <form method="POST" class="mb-4">
                        <input type="hidden" name="action" value="add_to_cart">
                        <div class="d-flex gap-3 mb-3">
                            <div class="quantity-selector">
                                <button type="button" class="quantity-btn" onclick="decreaseQuantity()">−</button>
                                <input type="number" name="quantity" id="quantity" class="quantity-input" value="1" min="1" max="<?php echo $medicine['stock_quantity']; ?>">
                                <button type="button" class="quantity-btn" onclick="increaseQuantity()">+</button>
                            </div>
                            
                            <button type="submit" class="btn btn-add-cart" 
                                    <?php echo $medicine['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                <i class="bi bi-cart-plus me-2"></i>
                                <?php echo $medicine['stock_quantity'] <= 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                            </button>
                            
                            <button type="button" class="btn btn-wishlist" onclick="toggleWishlist()">
                                <i class="bi bi-heart"></i>
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="d-flex gap-3 mb-4">
                        <a href="login.php" class="btn btn-add-cart text-center text-decoration-none">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login to Buy
                        </a>
                        <button class="btn btn-wishlist" disabled>
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                <?php endif; ?>
                
                <div class="row text-center">
                    <div class="col-4">
                        <i class="bi bi-building text-primary" style="font-size: 2rem;"></i>
                        <div class="mt-2">
                            <strong><?php echo htmlspecialchars($medicine['manufacturer'] ?? 'N/A'); ?></strong><br>
                            <small class="text-muted">Manufacturer</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <i class="bi bi-calendar-check text-primary" style="font-size: 2rem;"></i>
                        <div class="mt-2">
                            <strong><?php echo $medicine['expiry_date'] ? date('M Y', strtotime($medicine['expiry_date'])) : 'N/A'; ?></strong><br>
                            <small class="text-muted">Expiry</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <i class="bi bi-tag text-primary" style="font-size: 2rem;"></i>
                        <div class="mt-2">
                            <strong><?php echo htmlspecialchars($category_name); ?></strong><br>
                            <small class="text-muted">Category</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Information Tabs -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="info-tabs">
                    <button class="nav-tab active" onclick="showTab('description')">Description</button>
                    <button class="nav-tab" onclick="showTab('uses')">Uses</button>
                    <button class="nav-tab" onclick="showTab('dosage')">Dosage</button>
                    <button class="nav-tab" onclick="showTab('side-effects')">Side Effects</button>
                </div>
                
                <div id="description" class="tab-content">
                    <h4>Product Description</h4>
                    <p><?php echo nl2br(htmlspecialchars($medicine['description'] ?? 'No description available.')); ?></p>
                </div>
                
                <div id="uses" class="tab-content" style="display: none;">
                    <h4>Uses</h4>
                    <p>This medication is used for treating various conditions as prescribed by your healthcare provider.</p>
                    <ul>
                        <li>Follow your doctor's instructions carefully</li>
                        <li>Read the patient information leaflet provided</li>
                        <li>Consult your pharmacist for any questions</li>
                    </ul>
                </div>
                
                <div id="dosage" class="tab-content" style="display: none;">
                    <h4>Dosage Information</h4>
                    <p><strong>Important:</strong> Always follow the dosage prescribed by your healthcare provider.</p>
                    <ul>
                        <li>Take exactly as prescribed by your doctor</li>
                        <li>Do not exceed the recommended dose</li>
                        <li>Take with or without food as directed</li>
                        <li>Complete the full course of treatment</li>
                    </ul>
                </div>
                
                <div id="side-effects" class="tab-content" style="display: none;">
                    <h4>Possible Side Effects</h4>
                    <p>Like all medicines, this medicine can cause side effects, although not everybody gets them.</p>
                    <p><strong>Common side effects may include:</strong></p>
                    <ul>
                        <li>Nausea or stomach upset</li>
                        <li>Headache</li>
                        <li>Dizziness</li>
                        <li>Drowsiness</li>
                    </ul>
                    <p><strong>Seek medical attention immediately if you experience:</strong></p>
                    <ul>
                        <li>Severe allergic reactions</li>
                        <li>Difficulty breathing</li>
                        <li>Chest pain</li>
                        <li>Severe dizziness</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
            <div class="row mt-5">
                <div class="col-12">
                    <h3 class="mb-4">Related Products</h3>
                    <div class="row">
                        <?php foreach ($related_products as $product): ?>
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card related-product">
                                    <div class="related-image">
                                        <?php if ($product['image_url']): ?>
                                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                 style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="text-muted">
                                                <i class="bi bi-image" style="font-size: 2rem;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                                        <div class="mb-2">
                                            <span class="fw-bold text-primary">₹<?php echo number_format($product['discount_price'] ?? $product['price'], 2); ?></span>
                                            <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                                                <small class="text-muted text-decoration-line-through">₹<?php echo number_format($product['price'], 2); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary w-100">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
    
    <script>
        function changeImage(thumbnail) {
            const mainImage = document.getElementById('mainImage');
            if (mainImage) {
                mainImage.src = thumbnail.src;
                
                // Update active thumbnail
                document.querySelectorAll('.thumbnail').forEach(thumb => {
                    thumb.classList.remove('active');
                });
                thumbnail.classList.add('active');
            }
        }
        
        function increaseQuantity() {
            const input = document.getElementById('quantity');
            const max = parseInt(input.getAttribute('max'));
            if (input.value < max) {
                input.value = parseInt(input.value) + 1;
            }
        }
        
        function decreaseQuantity() {
            const input = document.getElementById('quantity');
            if (input.value > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }
        
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Remove active class from all nav tabs
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).style.display = 'block';
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }
        
        function toggleWishlist() {
            // This would typically make an AJAX call to toggle wishlist
            const button = event.target.closest('.btn-wishlist');
            button.classList.toggle('active');
            
            const icon = button.querySelector('i');
            if (button.classList.contains('active')) {
                icon.classList.remove('bi-heart');
                icon.classList.add('bi-heart-fill');
                showAlert('Added to wishlist!', 'success');
            } else {
                icon.classList.remove('bi-heart-fill');
                icon.classList.add('bi-heart');
                showAlert('Removed from wishlist', 'info');
            }
        }
    </script>
</body>
</html>
