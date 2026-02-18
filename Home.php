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

        /* image ne change karv mate specific time e*/
        .carousel-item img {
            height: 520px;
            object-fit: cover;
        }

        .carousel-caption {
            background: rgba(0, 0, 0, 0.6);
            padding: 30px;
            border-radius: 20px;
        }

        /* ema lakhela caption ne font set karva mate*/
        .carousel-caption h2 {
            font-weight: 500;
        }

        /* tile ma about kk pharam ne lakhyu che eni font size
        set karva mate*/
        .section-title {
            font-weight: 700;
            margin-bottom: 15px;
        }

        /* section about ni niche rakhela batha eriting ne proper formate ma set karva mate*/
        .section-subtitle {
            color: #6c757d;
            max-width: 700px;
            margin: auto;
        }

        /* information box ne set karva mate ena arrow jai tyare set karva mate*/
        .info-box {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            transition: 0.3s;
            height: 100%;
        }

        /* box na icons ne color aapva mate ne padding set karva mate*/
        .info-box i {
            font-size: 40px;
            color: #00a8ff;
            margin-bottom: 15px;
        }

        .info-box:hover {
            transform: translateY(-10px);
        }

        /* product mate */
        .product-box {
            background: linear-gradient(135deg, #ffffff, #f1f6fb);
            padding: 30px;
            border-radius: 22px;
            text-align: center;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
            transition: 0.3s;
            height: 100%;
        }

        /* product na icon ni size moti karva mate ne color change karva mate */
        .product-box i {
            font-size: 42px;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        /* ema arrow jai tyare color change karva mate */
        .product-box:hover {
            background: linear-gradient(135deg, #2c3e50, #1f2f3f);
            color: #fff;
        }

        /*product box na icon ne arrow lai jai tyare color change karva mate*/
        .product-box:hover i {
            color: #00d1b2;
        }

        /* */


        .stats h3 {
            font-weight: 700;
        }

        /*contanct mate  */
        .contact-box {
            background: #fff;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <!--navbar-->
    <div class="container">

        <div id="carouselExample" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-inner">

                <div class="carousel-item active">
                    <img src="gallary/front_!.png" class="d-block w-100">

                </div>

                <div class="carousel-item">
                    <img src="gallary/medicine-2.jpg" class="d-block w-100">
                    <div class="carousel-caption">
                        <h2>WHO-GMP Certified Manufacturing</h2>
                        <p>International standards you can rely on</p>
                    </div>
                </div>

                <div class="carousel-item">
                    <img src="gallary/largest-pharmaceutical-companies.jpg.webp" class="d-block w-100">
                    <div class="carousel-caption">
                        <h2>Innovation Driven Healthcare</h2>
                        <p>Research, technology and patient care combined</p>
                    </div>
                </div>

            </div>
        </div>

        <!-- ===== ABOUT ===== -->
        <section id="about" class="py-5">
            <div class="container text-center">
                <h2 class="section-title">About KK Pharma</h2>
                <p class="section-subtitle mb-5">
                    KK Pharma is a fast-growing pharmaceutical company committed to manufacturing
                    high-quality, affordable and reliable medicines that improve lives across the globe.
                </p>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="info-box">
                            <i class="bi bi-heart-pulse"></i>
                            <h5>Our Mission</h5>
                            <p>To enhance patient wellbeing by providing safe, effective and affordable medicines.</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <i class="bi bi-globe2"></i>
                            <h5>Our Vision</h5>
                            <p>To become a globally trusted pharmaceutical brand delivering excellence.</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <i class="bi bi-shield-check"></i>
                            <h5>Our Values</h5>
                            <p>Quality, Integrity, Innovation and Patient-Centric Care.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!--status-->
        <section class="container my-5">
            <div class="stats row text-center">
                <div class="col-md-3">
                    <h3>10+</h3>
                    <p>Years of Experience</p>
                </div>
                <div class="col-md-3">
                    <h3>150+</h3>
                    <p>Products Portfolio</p>
                </div>
                <div class="col-md-3">
                    <h3>20+</h3>
                    <p>Countries Served</p>
                </div>
                <div class="col-md-3">
                    <h3>100%</h3>
                    <p>Quality Compliance</p>
                </div>
            </div>
        </section>

        <!-- product box mate container lidhu-->
        <section id="products" class="py-5 bg-light">
            <div class="container text-center">
                <h2 class="section-title">Our Product Range</h2>
                <p class="section-subtitle mb-5">
                    We offer a comprehensive portfolio covering multiple therapeutic segments.
                </p>

                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="product-box">
                            <i class="bi bi-capsule"></i>
                            <h6>Tablets</h6>
                            <p>Antibiotics, Analgesics, Anti-inflammatory & Vitamins</p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="product-box">
                            <i class="bi bi-droplet"></i>
                            <h6>Syrups</h6>
                            <p>Pediatric, Cough & Nutritional Formulations</p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="product-box">
                            <i class="bi bi-prescription2"></i>
                            <h6>Capsules</h6>
                            <p>Nutraceuticals and Specialty Supplements</p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="product-box">
                            <i class="bi bi-hospital"></i>
                            <h6>Injectables</h6>
                            <p>Critical Care & Hospital Use Medicines</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- contact mate container lidhu-->
        <section id="contact" class="py-5">
            <div class="container">
                <h2 class="section-title text-center mb-4">Contact Us</h2>

                <div class="row justify-content-center">
                    <div class="col-md-7">
                        <div class="contact-box text-center">
                            <h5>KK Pharma Pvt. Ltd.</h5>
                            <p>Email: info@kkpharma.com</p>
                            <p>Phone: +91 98765 43210</p>
                            <p>Ahmedabad, Gujarat, India</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
include_once "footer.php"
?>