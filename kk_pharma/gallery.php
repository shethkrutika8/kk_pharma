<?php include_once 'Database/db_config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>KK Pharma | Gallery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #1f2f3f;
            --secondary: #718497;
            --accent: #00d1b2;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f7fb;
            color: #2c3e50;
        }

        /* NAVBAR */
        .navbar {
            background: linear-gradient(90deg, var(--primary), #16222e);
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }

        .nav-link {
            color: #fff !important;
            margin-left: 18px;
            font-weight: 500;
            transition: 0.3s;
        }

        .nav-link:hover {
            color: var(--accent) !important;
        }

        .navbar-brand {
            font-size: 22px;
            letter-spacing: 1px;
        }

        .navbar-toggler {
            box-shadow: none !important;
        }

        /* header na formate ma photo mukva mate and ene different show karva mate*/
        
        .page-header {
            background: linear-gradient(rgba(44, 62, 80, 0.85),
                    rgba(44, 62, 80, 0.85)),
                url("<?php echo get_image('gallery', 'header_bg', 'gallary/largest-pharmaceutical-companies.jpg.webp'); ?>") center/cover;
            padding: 80px 0;
            color: #fff;
            text-align: center;
        }

        /* galary na image ni border ne round shape ma lavva mate */
        .gallery-item { 
            border-radius: 20px;
            overflow: hidden; /*ana thi thai che*/
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
            transition: 0.3s;
            background: #fff;
            position: relative;
        }

    /* image ne setting karva mate  */     
        .gallery-item img {
            width: 100%;
            height: 260px;
            object-fit: cover;
            transition: 0.4s; /*image ne proper way ma set transition karva mate*/
        }

        /* gallary item ne hover means upside batava mate jyare arrow lai jai tayre*/
        .gallery-item:hover img {
            transform: scale(1.1);
        } 

        /* content lakelo che tya proper box na formate ma batava mate*/
        .content-box {
            background: #ffffff;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(31, 47, 63, 0.9);
            color: white;
            padding: 15px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .gallery-item:hover .gallery-overlay {
            transform: translateY(0);
        }

        .gallery-overlay h6 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .gallery-overlay p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        
    </style>
</head>

<body>

<?php include 'header.php'; ?>

    <!-- HEADER -->
    <section class="page-header">
        <div class="container">
            <h1><?php echo htmlspecialchars(get_content('gallery_header_title', 'Our Gallery')); ?></h1>
            <p><?php echo htmlspecialchars(get_content('gallery_header_subtitle', 'Snapshots of our work environment, facilities, and pharmaceutical operations')); ?></p>
        </div>
    </section>

    <!-- INTRO CONTENT -->
    <section class="py-5">
        <div class="container">
            <div class="content-box text-center">
                <h3 class="mb-3"><?php echo htmlspecialchars(get_content('gallery_intro_title', 'A Visual Look at KK Pharma')); ?></h3>
                <p class="mb-0">
                    <?php echo htmlspecialchars(get_content('gallery_intro_desc', 'Welcome to our gallery section where we share visual highlights of our workplace, operational setup, product handling, and infrastructure. These images reflect our commitment to maintaining professional standards, organized processes, and a safe working environment across all our activities.')); ?>
                </p>
            </div>
        </div>
    </section>

    <!-- GALLERY -->
    <section class="pb-5">
        <div class="container">
            <div class="row g-4">

                <div class="col-md-4">
                    <div class="gallery-item">
                        <img src="<?php echo get_image('gallery', 'item1', 'gallary/manufacturing_unti.jfif'); ?>" alt="<?php echo htmlspecialchars(get_content('gallery_item1_title', 'Production Area')); ?>">
                        <div class="gallery-overlay">
                            <h6><?php echo htmlspecialchars(get_content('gallery_item1_title', 'Production Area')); ?></h6>
                            <p><?php echo htmlspecialchars(get_content('gallery_item1_desc', 'Organized and monitored workflow setup')); ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="gallery-item">
                        <img src="<?php echo get_image('gallery', 'item2', 'gallary/medicine-2.jpg'); ?>" alt="<?php echo htmlspecialchars(get_content('gallery_item2_title', 'Medicine Range')); ?>">
                        <div class="gallery-overlay">
                            <h6><?php echo htmlspecialchars(get_content('gallery_item2_title', 'Medicine Range')); ?></h6>
                            <p><?php echo htmlspecialchars(get_content('gallery_item2_desc', 'Carefully packaged pharma products')); ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="gallery-item">
                        <img src="<?php echo get_image('gallery', 'item3', 'gallary/reasearch unit.jpg'); ?>" alt="<?php echo htmlspecialchars(get_content('gallery_item3_title', 'Research Workspace')); ?>">
                        <div class="gallery-overlay">
                            <h6><?php echo htmlspecialchars(get_content('gallery_item3_title', 'Research Workspace')); ?></h6>
                            <p><?php echo htmlspecialchars(get_content('gallery_item3_desc', 'Focused development environment')); ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="gallery-item">
                        <img src="<?php echo get_image('gallery', 'item4', 'gallary/injection.jpg'); ?>" alt="<?php echo htmlspecialchars(get_content('gallery_item4_title', 'Process Line')); ?>">
                        <div class="gallery-overlay">
                            <h6><?php echo htmlspecialchars(get_content('gallery_item4_title', 'Process Line')); ?></h6>
                            <p><?php echo htmlspecialchars(get_content('gallery_item4_desc', 'Precision handling procedures')); ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="gallery-item">
                        <img src="<?php echo get_image('gallery', 'item5', 'gallary/warehouse-interior-with-forklifts-shelving.jpg'); ?>" alt="<?php echo htmlspecialchars(get_content('gallery_item5_title', 'Storage Facility')); ?>">
                        <div class="gallery-overlay">
                            <h6><?php echo htmlspecialchars(get_content('gallery_item5_title', 'Storage Facility')); ?></h6>
                            <p><?php echo htmlspecialchars(get_content('gallery_item5_desc', 'Structured inventory management')); ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="gallery-item">
                        <img src="<?php echo get_image('gallery', 'item6', 'gallary/quality.webp'); ?>" alt="<?php echo htmlspecialchars(get_content('gallery_item6_title', 'Quality Check')); ?>">
                        <div class="gallery-overlay">
                            <h6><?php echo htmlspecialchars(get_content('gallery_item6_title', 'Quality Check')); ?></h6>
                            <p><?php echo htmlspecialchars(get_content('gallery_item6_desc', 'Inspection and verification stage')); ?></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- BOTTOM CONTENT -->
    <section class="pb-5">
        <div class="container">
            <div class="content-box text-center">
                <h4 class="mb-3"><?php echo htmlspecialchars(get_content('gallery_bottom_title', 'Commitment to Quality & Care')); ?></h4>
                <p class="mb-0">
                    <?php echo htmlspecialchars(get_content('gallery_bottom_desc', 'At KK Pharma, we focus on consistency, cleanliness, and controlled processes. Our teams follow structured workflows and responsible handling practices to ensure reliability and safety in every stage of operation. We continuously work toward improving efficiency and maintaining trust through disciplined execution.')); ?>
                </p>
            </div>
        </div>
    </section>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- ===== FOOTER ===== -->
<footer class="text-center py-2 mt-3" style="background: #1f2f3f; color: #fff;">
    <img src="gallary/kk pahrma.png" alt="KK Pharma Logo" style="height: 30px;">
    <p class="mb-0 small">© 2026 KK Pharma. All Rights Reserved.</p>
</footer>

</body>

</html>