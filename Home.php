<?php
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
include_once "footer.php";
?>
