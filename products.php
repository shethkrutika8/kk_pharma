<?php
require_once 'kk_pharma/after_login/config.php';

// Get filter parameters
$category_id = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'name';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];
$types = '';

if (!empty($category_id)) {
    $where_conditions[] = "m.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

if (!empty($search)) {
    $where_conditions[] = "(m.name LIKE ? OR m.description LIKE ? OR m.manufacturer LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Sorting
$order_by = match($sort) {
    'name' => 'm.name ASC',
    'price_low' => 'm.price ASC',
    'price_high' => 'm.price DESC',
    'rating' => 'm.rating DESC',
    'newest' => 'm.created_at DESC',
    default => 'm.name ASC'
};

// Get total count
$count_sql = "SELECT COUNT(DISTINCT m.id) as total FROM medicines m LEFT JOIN categories c ON m.category_id = c.id $where_clause";
$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_products = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $per_page);

// Get products
$sql = "SELECT m.*, c.name as category_name 
        FROM medicines m 
        LEFT JOIN categories c ON m.category_id = c.id 
        $where_clause 
        ORDER BY $order_by 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();

$products = [];
$result = $stmt->get_result();
while ($product = $result->fetch_assoc()) {
    $products[] = $product;
}

// Get categories for filter
$categories_result = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories = [];
while ($category = $categories_result->fetch_assoc()) {
    $categories[] = $category;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | KK Pharma</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/dashboard.css" rel="stylesheet">
    
    <style>
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .product-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .product-image {
            height: 200px;
            object-fit: cover;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-prescription {
            background: #dc3545;
            color: white;
        }
        
        .badge-featured {
            background: #28a745;
            color: white;
        }
        
        .product-body {
            padding: 1.25rem;
        }
        
        .product-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            line-height: 1.4;
            height: 2.8rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .product-category {
            color: #6c757d;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }
        
        .stars {
            color: #ffc107;
        }
        
        .rating-count {
            color: #6c757d;
            font-size: 0.875rem;
        }
        
        .product-price {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .current-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent-color);
        }
        
        .original-price {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 0.875rem;
        }
        
        .discount-badge {
            background: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-cart {
            flex: 1;
            background: var(--accent-color);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-cart:hover {
            background: #00b894;
            transform: translateY(-1px);
        }
        
        .btn-wishlist {
            background: white;
            border: 2px solid #e9ecef;
            color: #6c757d;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .btn-wishlist:hover,
        .btn-wishlist.active {
            border-color: #dc3545;
            color: #dc3545;
            background: #fff5f5;
        }
        
        .filter-sidebar {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }
        
        .search-box {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .category-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .category-pill {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            border: 2px solid #e9ecef;
            background: white;
            color: #495057;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .category-pill:hover,
        .category-pill.active {
            background: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
        }
        
        .sort-dropdown {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.5rem 1rem;
        }
        
        .stock-info {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
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
                        <a class="nav-link active" href="products.php">
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

    <!-- Main Content -->
    <div class="container-fluid py-4">
        <!-- Search and Filters -->
        <div class="search-box">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search for medicines, brands, or symptoms..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-primary" type="submit">
                            Search
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select sort-dropdown" name="sort" onchange="this.form.submit()">
                        <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Sort by Name</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Category Pills -->
        <div class="category-pills mb-4">
            <a href="products.php" class="category-pill <?php echo empty($category_id) ? 'active' : ''; ?>">
                <i class="bi bi-grid me-1"></i>All Products
            </a>
            <?php foreach ($categories as $category): ?>
                <a href="products.php?category=<?php echo $category['id']; ?>" 
                   class="category-pill <?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Results Header -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="mb-0">
                    <?php if (!empty($search)): ?>
                        Search Results for "<?php echo htmlspecialchars($search); ?>"
                    <?php elseif (!empty($category_id)): ?>
                        <?php echo htmlspecialchars($categories[array_search($category_id, array_column($categories, 'id'))]['name'] ?? 'Category'); ?>
                    <?php else: ?>
                        All Products
                    <?php endif; ?>
                    <span class="text-muted">(<?php echo $total_products; ?> products)</span>
                </h5>
            </div>
        </div>

        <!-- Products Grid -->
        <?php if (!empty($products)): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="card product-card">
                        <div class="product-image">
                            <?php if ($product['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div class="text-muted">
                                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($product['prescription_required']): ?>
                                <span class="product-badge badge-prescription">
                                    <i class="bi bi-file-medical me-1"></i>Prescription
                                </span>
                            <?php elseif ($product['is_featured']): ?>
                                <span class="product-badge badge-featured">
                                    <i class="bi bi-star me-1"></i>Featured
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-body">
                            <h6 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></div>
                            
                            <div class="product-rating">
                                <div class="stars">
                                    <?php
                                    $rating = round($product['rating'] ?? 0);
                                    for ($i = 1; $i <= 5; $i++):
                                        echo $i <= $rating ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                                    endfor;
                                    ?>
                                </div>
                                <span class="rating-count">(<?php echo $product['review_count'] ?? 0; ?>)</span>
                            </div>
                            
                            <div class="stock-info">
                                <?php if ($product['stock_quantity'] > 10): ?>
                                    <span class="in-stock"><i class="bi bi-check-circle me-1"></i>In Stock (<?php echo $product['stock_quantity']; ?>)</span>
                                <?php elseif ($product['stock_quantity'] > 0): ?>
                                    <span class="low-stock"><i class="bi bi-exclamation-triangle me-1"></i>Only <?php echo $product['stock_quantity']; ?> left</span>
                                <?php else: ?>
                                    <span class="out-of-stock"><i class="bi bi-x-circle me-1"></i>Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-price">
                                <span class="current-price">₹<?php echo number_format($product['discount_price'] ?? $product['price'], 2); ?></span>
                                <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                                    <span class="original-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                    <span class="discount-badge">
                                        <?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>% OFF
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-actions">
                                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                                    <form method="POST" action="user_dashboard/index.php" class="flex-grow-1">
                                        <input type="hidden" name="action" value="add_to_cart">
                                        <input type="hidden" name="medicine_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-cart w-100" 
                                                <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                            <i class="bi bi-cart-plus me-1"></i>
                                            <?php echo $product['stock_quantity'] <= 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="user_dashboard/index.php">
                                        <input type="hidden" name="action" value="add_to_wishlist">
                                        <input type="hidden" name="medicine_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="btn btn-wishlist">
                                            <i class="bi bi-heart"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-cart w-100">
                                        <i class="bi bi-cart-plus me-1"></i>Login to Buy
                                    </a>
                                    <button class="btn btn-wishlist" disabled>
                                        <i class="bi bi-heart"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-search" style="font-size: 4rem; color: #6c757d;"></i>
                <h5 class="mt-3">No products found</h5>
                <p class="text-muted">Try adjusting your search or filter criteria</p>
                <a href="products.php" class="btn btn-primary">View All Products</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>
