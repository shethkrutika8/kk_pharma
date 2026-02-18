<?php
include_once "header.php"
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>KK Pharma | Gallery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f7fb;
            color: #2c3e50;
        }
        /* header na formate ma photo mukva mate and ene different show karva mate*/
        
        .page-header {
            background: linear-gradient(rgba(44, 62, 80, 0.85),
                    rgba(44, 62, 80, 0.85)),
                url("gallary/largest-pharmaceutical-companies.jpg.webp") center/cover;
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

        
    </style>
</head>

<body>


    <!-- HEADER -->
    <section class="page-header">
        <div class="container">
            <h1>Our Gallery</h1>
            <p>Snapshots of our work environment, facilities, and pharmaceutical operations</p>
        </div>
    </section>

    <!-- INTRO CONTENT -->
    <section class="py-5">
        <div class="container">
            <div class="content-box text-center">
                <h3 class="mb-3">A Visual Look at KK Pharma</h3>
                <p class="mb-0">
                    Welcome to our gallery section where we share visual highlights of our workplace,
                    operational setup, product handling, and infrastructure. These images reflect our
                    commitment to maintaining professional standards, organized processes, and a safe
                    working environment across all our activities.
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
                        <img src="gallary/manufacturing_unti.jfif">
                        <div class="gallery-overlay">
                            <h6>Production Area</h6>
                            <p>Organized and monitored workflow setup</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="gallery-item">
                        <img src="gallary/medicine-2.jpg">
                        <div class="gallery-overlay">
                            <h6>Medicine Range</h6>
                            <p>Carefully packaged pharma products</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="gallery-item">
                        <img src="gallary/reasearch unit.jpg">
                        <div class="gallery-overlay">
                            <h6>Research Workspace</h6>
                            <p>Focused development environment</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="gallery-item">
                        <img src="gallary/injection.jpg">
                        <div class="gallery-overlay">
                            <h6>Process Line</h6>
                            <p>Precision handling procedures</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="gallery-item">
                        <img src="gallary/warehouse-interior-with-forklifts-shelving.jpg">
                        <div class="gallery-overlay">
                            <h6>Storage Facility</h6>
                            <p>Structured inventory management</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="gallery-item">
                        <img src="gallary/quality.webp">
                        <div class="gallery-overlay">
                            <h6>Quality Check</h6>
                            <p>Inspection and verification stage</p>
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
                <h4 class="mb-3">Commitment to Quality & Care</h4>
                <p class="mb-0">
                    At KK Pharma, we focus on consistency, cleanliness, and controlled processes.
                    Our teams follow structured workflows and responsible handling practices to ensure
                    reliability and safety in every stage of operation. We continuously work toward
                    improving efficiency and maintaining trust through disciplined execution.
                </p>
            </div>
        </div>
    </section>

   

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
<?php
include_once "footer.php"
?>