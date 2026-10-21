<?php
<<<<<<< HEAD
require_once 'kk_pharma/after_login/config.php';

// Get featured medicines for homepage
$featured_result = $conn->prepare("SELECT m.*, c.name as category_name FROM medicines m LEFT JOIN categories c ON m.category_id = c.id WHERE m.status = 'active' AND (m.is_featured = 1 OR m.rating >= 4.0) ORDER BY m.rating DESC LIMIT 8");
$featured_result->execute();
$featured_medicines = [];
while ($medicine = $featured_result->get_result()->fetch_assoc()) {
    $featured_medicines[] = $medicine;
}

// Get categories
$categories_result = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY COALESCE(sort_order, 0), name");
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
    <title>KK Pharma - Your Trusted Online Pharmacy</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/dashboard.css" rel="stylesheet">
    
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,133.3C960,128,1056,96,1152,90.7C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .category-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            cursor: pointer;
            height: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: white;
        }
        
        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .category-icon {
            font-size: 3rem;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }
        
        .feature-card {
            border: none;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-color), #00b894);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
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
            height: 180px;
            object-fit: cover;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .badge-featured {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .testimonial-card {
            border: none;
            border-radius: 15px;
            padding: 2rem;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 10px;
            left: 20px;
            font-size: 4rem;
            color: var(--accent-color);
            opacity: 0.3;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .stats-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--accent-color);
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
                        <a class="nav-link active" href="Home.php">
                            <i class="bi bi-house me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="bi bi-shop me-1"></i>Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
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
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="bi bi-person-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="display-4 fw-bold mb-4">Your Trusted Online Pharmacy</h1>
                        <p class="lead mb-4">Get authentic medicines delivered to your doorstep. Fast, reliable, and affordable healthcare solutions.</p>
                        <div class="d-flex gap-3">
                            <a href="products.php" class="btn btn-light btn-lg">
                                <i class="bi bi-shop me-2"></i>Shop Now
                            </a>
                            <a href="#features" class="btn btn-outline-light btn-lg">
                                <i class="bi bi-play-circle me-2"></i>Learn More
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="bi bi-capsule" style="font-size: 15rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Shop by Category</h2>
                <p class="lead text-muted">Find what you need from our wide range of categories</p>
            </div>
            
            <div class="row">
                <?php foreach ($categories as $category): ?>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="category-card" onclick="window.location.href='products.php?category=<?php echo $category['id']; ?>'">
                            <div class="category-icon">
                                <i class="bi bi-<?php echo $category['icon'] ?? 'grid'; ?>"></i>
                            </div>
                            <h5><?php echo htmlspecialchars($category['name']); ?></h5>
                            <p class="text-muted small"><?php echo htmlspecialchars($category['description'] ?? ''); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Why Choose KK Pharma?</h2>
                <p class="lead text-muted">We provide the best healthcare experience</p>
            </div>
            
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-truck"></i>
                        </div>
                        <h4>Fast Delivery</h4>
                        <p class="text-muted">Get your medicines delivered within 24 hours in major cities</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4>100% Authentic</h4>
                        <p class="text-muted">All medicines are sourced directly from manufacturers and authorized distributors</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-headset"></i>
                        </div>
                        <h4>24/7 Support</h4>
                        <p class="text-muted">Our customer support team is always here to help you</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                        <h4>Easy Returns</h4>
                        <p class="text-muted">Simple and hassle-free return policy within 7 days</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Featured Products</h2>
                <p class="lead text-muted">Popular medicines recommended by our customers</p>
            </div>
            
            <div class="row">
                <?php foreach ($featured_medicines as $medicine): ?>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card product-card">
                            <div class="product-image position-relative">
                                <?php if ($medicine['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($medicine['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($medicine['name']); ?>"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div class="text-muted">
                                        <i class="bi bi-image" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($medicine['is_featured']): ?>
                                    <span class="badge-featured">
                                        <i class="bi bi-star me-1"></i>Featured
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars($medicine['name']); ?></h6>
                                <p class="card-text small text-muted"><?php echo htmlspecialchars(substr($medicine['description'] ?? '', 0, 60)); ?>...</p>
                                
                                <div class="mb-2">
                                    <span class="fw-bold text-primary">₹<?php echo number_format($medicine['discount_price'] ?? $medicine['price'], 2); ?></span>
                                    <?php if ($medicine['discount_price'] && $medicine['discount_price'] < $medicine['price']): ?>
                                        <small class="text-muted text-decoration-line-through">₹<?php echo number_format($medicine['price'], 2); ?></small>
                                    <?php endif; ?>
                                </div>
                                
                                <a href="product.php?id=<?php echo $medicine['id']; ?>" class="btn btn-sm btn-primary w-100">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="products.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-grid me-2"></i>View All Products
                </a>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="stats-card">
                        <div class="stats-number">500+</div>
                        <h5>Medicines</h5>
                        <p class="text-muted">Wide range of authentic medicines</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stats-card">
                        <div class="stats-number">50K+</div>
                        <h5>Happy Customers</h5>
                        <p class="text-muted">Trusted by thousands of customers</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stats-card">
                        <div class="stats-number">24/7</div>
                        <h5>Support</h5>
                        <p class="text-muted">Always here to help you</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stats-card">
                        <div class="stats-number">100%</div>
                        <h5>Authentic</h5>
                        <p class="text-muted">Guaranteed genuine products</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">What Our Customers Say</h2>
                <p class="lead text-muted">Real reviews from real customers</p>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card">
                        <div class="mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="mb-3">"Excellent service! Fast delivery and genuine medicines. Highly recommended!"</p>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <span>RK</span>
                            </div>
                            <div>
                                <h6 class="mb-0">Rahul Kumar</h6>
                                <small class="text-muted">Verified Customer</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card">
                        <div class="mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="mb-3">"Very convenient platform. Easy to order and medicines are always delivered on time."</p>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <span>SP</span>
                            </div>
                            <div>
                                <h6 class="mb-0">Sunita Patel</h6>
                                <small class="text-muted">Verified Customer</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card">
                        <div class="mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star text-warning"></i>
                        </div>
                        <p class="mb-3">"Good prices and authentic products. Customer support is very helpful."</p>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <span>AM</span>
                            </div>
                            <div>
                                <h6 class="mb-0">Amit Mehra</h6>
                                <small class="text-muted">Verified Customer</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="bi bi-hospital me-2"></i>KK Pharma</h5>
                    <p>Your trusted online pharmacy for authentic medicines and healthcare products.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-twitter fs-5"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-linkedin fs-5"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="Home.php" class="text-white text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="products.php" class="text-white text-decoration-none">Products</a></li>
                        <li class="mb-2"><a href="#about" class="text-white text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="#contact" class="text-white text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h6>Categories</h6>
                    <ul class="list-unstyled">
                        <?php foreach (array_slice($categories, 0, 5) as $category): ?>
                            <li class="mb-2">
                                <a href="products.php?category=<?php echo $category['id']; ?>" class="text-white text-decoration-none">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h6>Contact Info</h6>
                    <p class="mb-2"><i class="bi bi-geo-alt me-2"></i>123, Main Street, City</p>
                    <p class="mb-2"><i class="bi bi-telephone me-2"></i>+91 98765 43210</p>
                    <p class="mb-2"><i class="bi bi-envelope me-2"></i>info@kkpharma.com</p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">&copy; 2024 KK Pharma. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>
=======
include_once 'header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>KK Pharma | Quality Medicines</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f7fb;
            color: #7c9ec0;
        }

        .carousel-item img {
            height: 520px;
            object-fit: cover;
        }

        .carousel-caption {
            background: rgba(0, 0, 0, 0.6);
            padding: 30px;
            border-radius: 20px;
        }

        .carousel-caption h2 {
            font-weight: 500;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 15px;
        }

        .section-subtitle {
            color: #6c757d;
            max-width: 700px;
            margin: auto;
        }

        .info-box {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            transition: 0.3s;
            height: 100%;
        }

        .info-box i {
            font-size: 40px;
            color: #00a8ff;
            margin-bottom: 15px;
        }

        .info-box:hover {
            transform: translateY(-10px);
        }

        .product-box {
            background: linear-gradient(135deg, #ffffff, #f1f6fb);
            padding: 30px;
            border-radius: 22px;
            text-align: center;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
            transition: 0.3s;
            height: 100%;
        }

        .product-box i {
            font-size: 42px;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .product-box:hover {
            background: linear-gradient(135deg, #2c3e50, #1f2f3f);
            color: #fff;
        }

        .product-box:hover i {
            color: #00d1b2;
        }

        .stats h3 {
            font-weight: 700;
        }

        .contact-box {
            background: #fff;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <div class="container">
        <?php if (isset($_SESSION['login_success_msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show mt-3 mb-0" role="alert">
                <?php 
                echo $_SESSION['login_success_msg']; 
                unset($_SESSION['login_success_msg']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div id="carouselExample" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-inner">

                <div class="carousel-item active">
                    <img src="gallary/front_!.png" class="d-block w-100">
                </div>

                <div class="carousel-item">
                    <img src="gallary/medicine-2.jpg" class="d-block w-100">
                    <div class="carousel-caption">
                        <h2><?php echo htmlspecialchars(get_content('home_carousel1_title', 'WHO-GMP Certified Manufacturing')); ?></h2>
                        <p><?php echo htmlspecialchars(get_content('home_carousel1_subtitle', 'International standards you can rely on')); ?></p>
                    </div>
                </div>

                <div class="carousel-item">
                    <img src="gallary/largest-pharmaceutical-companies.jpg.webp" class="d-block w-100">
                    <div class="carousel-caption">
                        <h2><?php echo htmlspecialchars(get_content('home_carousel2_title', 'Innovation Driven Healthcare')); ?></h2>
                        <p><?php echo htmlspecialchars(get_content('home_carousel2_subtitle', 'Research, technology and patient care combined')); ?></p>
                    </div>
                </div>

            </div>
        </div>

        <!-- ===== ABOUT ===== -->
        <section id="about" class="py-5">
            <div class="container text-center">
                <h2 class="section-title"><?php echo htmlspecialchars(get_content('home_about_title', 'About KK Pharma')); ?></h2>
                <p class="section-subtitle mb-5">
                    <?php echo htmlspecialchars(get_content('home_about_desc', 'KK Pharma is a fast-growing pharmaceutical company committed to manufacturing high-quality, affordable and reliable medicines that improve lives across the globe.')); ?>
                </p>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="info-box">
                            <i class="bi bi-heart-pulse"></i>
                            <h5><?php echo htmlspecialchars(get_content('home_mission_title', 'Our Mission')); ?></h5>
                            <p><?php echo htmlspecialchars(get_content('home_mission_desc', 'To enhance patient wellbeing by providing safe, effective and affordable medicines.')); ?></p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <i class="bi bi-globe2"></i>
                            <h5><?php echo htmlspecialchars(get_content('home_vision_title', 'Our Vision')); ?></h5>
                            <p><?php echo htmlspecialchars(get_content('home_vision_desc', 'To become a globally trusted pharmaceutical brand delivering excellence.')); ?></p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <i class="bi bi-shield-check"></i>
                            <h5><?php echo htmlspecialchars(get_content('home_values_title', 'Our Values')); ?></h5>
                            <p><?php echo htmlspecialchars(get_content('home_values_desc', 'Quality, Integrity, Innovation and Patient-Centric Care.')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!--status-->
        <section class="container my-5">
            <div class="stats row text-center">
                <div class="col-md-3">
                    <h3><?php echo htmlspecialchars(get_content('home_stats_exp', '10+')); ?></h3>
                    <p><?php echo htmlspecialchars(get_content('home_stats_exp_text', 'Years of Experience')); ?></p>
                </div>
                <div class="col-md-3">
                    <h3><?php echo htmlspecialchars(get_content('home_stats_products', '150+')); ?></h3>
                    <p><?php echo htmlspecialchars(get_content('home_stats_products_text', 'Products Portfolio')); ?></p>
                </div>
                <div class="col-md-3">
                    <h3><?php echo htmlspecialchars(get_content('home_stats_countries', '20+')); ?></h3>
                    <p><?php echo htmlspecialchars(get_content('home_stats_countries_text', 'Countries Served')); ?></p>
                </div>
                <div class="col-md-3">
                    <h3><?php echo htmlspecialchars(get_content('home_stats_quality', '100%')); ?></h3>
                    <p><?php echo htmlspecialchars(get_content('home_stats_quality_text', 'Quality Compliance')); ?></p>
                </div>
            </div>
        </section>

        <section id="products" class="py-5 bg-light">
            <div class="container text-center">
                <h2 class="section-title"><?php echo htmlspecialchars(get_content('home_products_title', 'Our Product Range')); ?></h2>
                <p class="section-subtitle mb-5">
                    <?php echo htmlspecialchars(get_content('home_products_desc', 'We offer a comprehensive portfolio covering multiple therapeutic segments.')); ?>
                </p>

                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="product-box">
                            <i class="bi bi-capsule"></i>
                            <h6><?php echo htmlspecialchars(get_content('home_product1_title', 'Tablets')); ?></h6>
                            <p><?php echo htmlspecialchars(get_content('home_product1_desc', 'Antibiotics, Analgesics, Anti-inflammatory & Vitamins')); ?></p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="product-box">
                            <i class="bi bi-droplet"></i>
                            <h6><?php echo htmlspecialchars(get_content('home_product2_title', 'Syrups')); ?></h6>
                            <p><?php echo htmlspecialchars(get_content('home_product2_desc', 'Pediatric, Cough & Nutritional Formulations')); ?></p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="product-box">
                            <i class="bi bi-prescription2"></i>
                            <h6><?php echo htmlspecialchars(get_content('home_product3_title', 'Capsules')); ?></h6>
                            <p><?php echo htmlspecialchars(get_content('home_product3_desc', 'Nutraceuticals and Specialty Supplements')); ?></p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="product-box">
                            <i class="bi bi-hospital"></i>
                            <h6><?php echo htmlspecialchars(get_content('home_product4_title', 'Injectables')); ?></h6>
                            <p><?php echo htmlspecialchars(get_content('home_product4_desc', 'Critical Care & Hospital Use Medicines')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact" class="py-5">
            <div class="container">
                <h2 class="section-title text-center mb-4">Contact Us</h2>

                <div class="row justify-content-center">
                    <div class="col-md-7">
                        <div class="contact-box text-center">
                            <h5><?php echo htmlspecialchars(get_content('contact_info_title', 'KK Pharma Pvt. Ltd.')); ?></h5>
                            <p>Email: <?php echo htmlspecialchars(get_content('contact_email', 'info@kkpharma.com')); ?></p>
                            <p>Phone: <?php echo htmlspecialchars(get_content('contact_phone', '+91 98765 43210')); ?></p>
                            <p><?php echo htmlspecialchars(get_content('contact_address', 'Ahmedabad, Gujarat, India')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

</body>
</html>

<?php
include_once "footer.php"
?>
>>>>>>> c6775067838e23982daa6c2b4f2a0e39c1aeb335
